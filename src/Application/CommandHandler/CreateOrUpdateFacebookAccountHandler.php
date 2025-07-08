<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateFacebookAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Entity\User;
use App\Enum\SocialAccountStatus;
use App\Exception\OrganizationNotFoundException;
use App\Exception\UserNotFoundException;
use App\Repository\OrganizationRepository;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateFacebookAccountHandler extends CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private UserRepository $userRepository,
        private OrganizationRepository $organizationRepository,
        private FacebookSocialAccountRepository $facebookSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($facebookSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateFacebookAccount $message): void
    {
        /** @var ?Organization $organization */
        $organization = $this->organizationRepository->findOneBy(['id' => (string) $message->organizationId]);

        if (null === $organization) {
            throw new OrganizationNotFoundException((string) $message->organizationId);
        }

        /** @var FacebookSocialAccount $facebookAccount */
        $facebookAccount = $this->getAccount(
            socialAccountId: $message->facebookAccount->id,
            organization: $organization,
            class: FacebookSocialAccount::class
        );

        $date = new \DateTime();
        $date->modify('+60 days');

        $facebookAccount
            ->setLink($message->facebookAccount->link)
            ->setUsername($message->facebookAccount->username)
            ->setSocialAccountId($message->facebookAccount->id)
            ->setOrganization($organization)
            ->setFollowers($message->facebookAccount->followers)
            ->setFollowings($message->facebookAccount->followings)
            ->setWebsite($message->facebookAccount->website)
            ->setEmail($message->facebookAccount->email)
            ->setAvatarUrl($message->facebookAccount->picture)
            ->setExpireAt($date)
            ->setToken($message->facebookToken->token);

        $this->facebookSocialAccountRepository->save($facebookAccount, true);

        if ($facebookAccount->getStatus() !== SocialAccountStatus::PENDING_VALIDATION->value) {
            return;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $facebookAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(360000000),
            new AmqpStamp('async-low'),
        ]);
    }
}
