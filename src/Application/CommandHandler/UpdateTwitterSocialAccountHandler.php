<?php

namespace App\Application\CommandHandler;

use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\UpdateTwitterSocialAccount;
use App\Application\Command\UploadSocialAccount;
use App\Application\Command\UploadTwitterSocialAccount;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\SocialAccount\SocialAccountRepository;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use App\Service\Publish\PublishServiceFactory;
use App\Service\SocialAccount\SocialAccountServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateTwitterSocialAccountHandler
{
    public function __construct(
        private TwitterSocialAccountRepository $twitterSocialAccountRepository,
        private LoggerInterface $logger,
        private SocialAccountServiceFactory $socialAccountServiceFactory,
    ) {
    }

    public function __invoke(UpdateTwitterSocialAccount $message): void
    {
        /** @var TwitterSocialAccount[] $socialAccounts */
        $socialAccounts = $this->twitterSocialAccountRepository->findBy([
            'socialAccountId' => $message->socialAccountId,
        ]);

        if (empty($socialAccounts)) {
            $this->logger->warning(sprintf('Failed to get social accounts: accounts with id [%s] was not found.', (string) $message->socialAccountId), ['id' => (string) $message->socialAccountId]);

            return;
        }

        array_map(
            fn(TwitterSocialAccount $socialAccount) => $this->twitterSocialAccountRepository->save(
                $socialAccount
                    ->setUsername($message->twitterAccount->username)
                    ->setSocialAccountId($message->twitterAccount->id)
                    ->setFollowers($message->twitterAccount->publicMetrics->followers)
                    ->setFollowings($message->twitterAccount->publicMetrics->followings)
                    ->setLikes($message->twitterAccount->publicMetrics->likes)
                    ->setTweets($message->twitterAccount->publicMetrics->tweets)
                    ->setName($message->twitterAccount->name)
                    ->setAvatarUrl($message->twitterAccount->profileImageUrl)
                    ->setIsVerified($message->twitterAccount->verified),
                true
            ),
            $socialAccounts
        );
    }
}
