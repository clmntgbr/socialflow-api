<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateTwitterAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Group;
use App\Entity\SocialAccount\TokenSocialAccount;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Exception\GroupNotFoundException;
use App\Repository\GroupRepository;
use App\Repository\SocialAccount\TokenSocialAccountRepository;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateTwitterAccountHandler extends CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private TokenSocialAccountRepository $tokenSocialAccountRepository,
        private TwitterSocialAccountRepository $twitterSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($twitterSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateTwitterAccount $message): void
    {
        /** @var ?Group $group */
        $group = $this->groupRepository->findOneBy(['id' => (string) $message->groupId]);

        if (null === $group) {
            throw new GroupNotFoundException((string) $message->groupId);
        }

        /** @var TwitterSocialAccount $twitterAccount */
        $twitterAccount = $this->getAccount(
            socialAccountId: $message->twitterAccount->id,
            group: $group,
            class: TwitterSocialAccount::class
        );

        $token = $this->tokenSocialAccountRepository->findOneBy(['socialAccountId' => $message->twitterAccount->id]);

        if (null === $token) {
            $token = new TokenSocialAccount();
        }

        $token
            ->setSocialAccountId($message->twitterAccount->id)
            ->setToken($message->twitterToken->oauthToken)
            ->setTokenSecret($message->twitterToken->oauthTokenSecret);

        $twitterAccount
            ->setUsername($message->twitterAccount->username)
            ->setSocialAccountId($message->twitterAccount->id)
            ->setGroup($group)
            ->setFollowers($message->twitterAccount->publicMetrics->followers)
            ->setFollowings($message->twitterAccount->publicMetrics->followings)
            ->setLikes($message->twitterAccount->publicMetrics->likes)
            ->setTweets($message->twitterAccount->publicMetrics->tweets)
            ->setName($message->twitterAccount->name)
            ->setTokenSocialAccount($token)
            ->setAvatarUrl($message->twitterAccount->profileImageUrl)
            ->setIsVerified($message->twitterAccount->verified);

        $this->twitterSocialAccountRepository->save($twitterAccount, true);

        if ($twitterAccount->getStatus() !== SocialAccountStatus::PENDING_ACTIVATION->value) {
            return;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $twitterAccount->getId(), status: SocialAccountStatus::PENDING_ACTIVATION), [
            new DelayStamp(360000000),
            new AmqpStamp('async-low'),
        ]);
    }
}
