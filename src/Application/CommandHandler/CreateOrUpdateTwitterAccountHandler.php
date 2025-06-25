<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateTwitterAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\OrganizationRepository;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;
use Symfony\Component\Uid\Uuid;

#[AsMessageHandler]
final class CreateOrUpdateTwitterAccountHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private OrganizationRepository $organizationRepository,
        private TwitterSocialAccountRepository $twitterSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CreateOrUpdateTwitterAccount $message): ?Uuid
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

        $twitterAccount = $this->getTwitterAccount($message, $organization);

        $twitterAccount
            ->setUsername($message->twitterAccount->username)
            ->setSocialAccountId($message->twitterAccount->id)
            ->setOrganization($organization)
            ->setFollowers($message->twitterAccount->publicMetrics->followers)
            ->setFollowings($message->twitterAccount->publicMetrics->followings)
            ->setLikes($message->twitterAccount->publicMetrics->likes)
            ->setTweets($message->twitterAccount->publicMetrics->tweets)
            ->setName($message->twitterAccount->name)
            ->setAvatarUrl($message->twitterAccount->profileImageUrl)
            ->setIsVerified($message->twitterAccount->verified)
            ->setToken($message->twitterToken->oauthToken)
            ->setTokenSecret($message->twitterToken->oauthTokenSecret);

        $this->twitterSocialAccountRepository->save($twitterAccount, true);

        if ($twitterAccount->getStatus() !== SocialAccountStatus::PENDING_VALIDATION->getValue()) {
            return null;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $twitterAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(3600000),
            new AmqpStamp('async'),
        ]);

        return $twitterAccount->getId();
    }

    private function getTwitterAccount(CreateOrUpdateTwitterAccount $message, Organization $organization): TwitterSocialAccount
    {
        /** @var ?TwitterSocialAccount $twitterAccount */
        $twitterAccount = $this->twitterSocialAccountRepository->findOneBy([
            'organization' => $organization,
            'socialAccountId' => $message->twitterAccount->id,
        ]);

        if (null === $twitterAccount) {
            return new TwitterSocialAccount($message->accountId);
        }

        if ($twitterAccount->getStatus() === SocialAccountStatus::EXPIRED->getValue()) {
            $twitterAccount->setStatus(SocialAccountStatus::ACTIVE->getValue());
        }

        return $twitterAccount;
    }
}
