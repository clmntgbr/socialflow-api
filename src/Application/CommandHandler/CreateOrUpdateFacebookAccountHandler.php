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
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Uid\Uuid;

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

    public function __invoke(CreateOrUpdateFacebookAccount $message): ?Uuid
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

        $facebookAccount = $this->getFacebookAccount($message, $organization);

        $facebookAccount
            ->setLink($message->facebookAccount->link)
            ->setId($message->accountId)
            ->setUsername($message->facebookAccount->username)
            ->setSocialAccountId($message->facebookAccount->id)
            ->setOrganization($organization)
            ->setFollowers($message->facebookAccount->followers)
            ->setFollowings($message->facebookAccount->followings)
            ->setWebsite($message->facebookAccount->website)
            ->setEmail($message->facebookAccount->email)
            ->setAvatarUrl($message->facebookAccount->picture)
            ->setToken($message->facebookToken->token);

        $this->facebookSocialAccountRepository->save($facebookAccount, true);

        if ($facebookAccount->getStatus()->getValue() !== SocialAccountStatus::PENDING_VALIDATION->getValue()) {
            return null;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $facebookAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(3600000),
            new AmqpStamp('async'),
        ]);
        
        return $facebookAccount->getId();
    }

    private function getFacebookAccount(CreateOrUpdateFacebookAccount $message, Organization $organization): FacebookSocialAccount
    {
        /** @var ?FacebookSocialAccount $facebookAccount */
        $facebookAccount = $this->facebookSocialAccountRepository->findOneBy([
            'organization' => $organization,
            'socialAccountId' => $message->facebookAccount->id,
        ]);

        if (null === $facebookAccount) {
            return new FacebookSocialAccount($message->accountId);
        }

        if ($facebookAccount->getStatus()->getValue() === SocialAccountStatus::EXPIRED->getValue()) {
            $facebookAccount->setStatus(SocialAccountStatus::ACTIVE->getValue());
        }

        return $facebookAccount;
    }
}
