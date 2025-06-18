<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateFacebookAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\OrganizationRepository;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Console\Messenger\RunCommandMessage;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateFacebookAccountHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OrganizationRepository $organizationRepository,
        private FacebookSocialAccountRepository $facebookSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreateOrUpdateFacebookAccount $message): void
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['id' => (string) $message->userId]);

        if (null === $user) {
            throw new \Exception(sprintf('User does not exist with id [%s]', (string) $message->userId));
        }

        /** @var ?Organization $organization */
        $organization = $this->organizationRepository->findOneBy(['id' => (string) $message->organizationId]);

        if (null === $organization) {
            throw new \Exception(sprintf('Organization does not exist with id [%s]', (string) $message->organizationId));
        }

        /** @var ?FacebookSocialAccount $facebookAccount */
        $facebookAccount = $this->facebookSocialAccountRepository->findOneBy([
            'organization' => $organization,
            'socialAccountId' => $message->facebookAccount->id,
        ]);

        if (null === $facebookAccount) {
            $facebookAccount = new FacebookSocialAccount($message->accountId);
        }

        $facebookAccount
            ->setUsername($message->facebookAccount->username)
            ->setSocialAccountId($message->facebookAccount->id)
            ->setOrganization($organization)
            ->setFollower($message->facebookAccount->follower)
            ->setFollowing($message->facebookAccount->following)
            ->setLink($message->facebookAccount->link)
            ->setWebsite($message->facebookAccount->website)
            ->setEmail($message->facebookAccount->email)
            ->setAvatarUrl($message->facebookAccount->picture)
            ->setToken($message->facebookToken->token);

        $this->facebookSocialAccountRepository->save($facebookAccount, true);

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $facebookAccount->getId(), status: SocialAccountStatus::TO_VALIDATE), [
            new DelayStamp(3600000),
            new AmqpStamp('async'),
        ]);
    }
}
