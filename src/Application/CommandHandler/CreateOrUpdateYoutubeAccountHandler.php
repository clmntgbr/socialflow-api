<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateYoutubeAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Group;
use App\Entity\SocialAccount\TokenSocialAccount;
use App\Entity\SocialAccount\YoutubeSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Exception\GroupNotFoundException;
use App\Repository\GroupRepository;
use App\Repository\SocialAccount\TokenSocialAccountRepository;
use App\Repository\SocialAccount\YoutubeSocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\DelayStamp;

#[AsMessageHandler]
final class CreateOrUpdateYoutubeAccountHandler extends CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private TokenSocialAccountRepository $tokenSocialAccountRepository,
        private YoutubeSocialAccountRepository $youtubeSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($youtubeSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateYoutubeAccount $message): void
    {
        /** @var ?Group $group */
        $group = $this->groupRepository->findOneBy(['id' => (string) $message->groupId]);

        if (null === $group) {
            throw new GroupNotFoundException((string) $message->groupId);
        }

        /** @var YoutubeSocialAccount $youtubeAccount */
        $youtubeAccount = $this->getAccount(
            socialAccountId: $message->youtubeAccount->id,
            group: $group, class: YoutubeSocialAccount::class
        );

        $date = new \DateTime();
        $date->modify(sprintf('+%s seconds', $message->youtubeToken->expiresIn));

        $token = $this->tokenSocialAccountRepository->findOneBy(['socialAccountId' => $message->youtubeAccount->id]);

        if (null === $token) {
            $token = new TokenSocialAccount();
        }
    
        $token
            ->setSocialAccountId($message->youtubeAccount->id)
            ->setToken($message->youtubeToken->token)
            ->setRefreshTokenAndExpireAt($message->youtubeToken->refreshToken, $date);

        $youtubeAccount
            ->setDescription($message->youtubeAccount->description)
            ->setName($message->youtubeAccount->name)
            ->setUsername($message->youtubeAccount->username)
            ->setSocialAccountId($message->youtubeAccount->id)
            ->setGroup($group)
            ->setFollowers($message->youtubeAccount->publicMetrics->followers)
            ->setAvatarUrl($message->youtubeAccount->picture)
            ->setTokenSocialAccount($token)
            ->setIsVerified($message->youtubeAccount->verified);

        $this->youtubeSocialAccountRepository->save($youtubeAccount, true);

        if ($youtubeAccount->getStatus() !== SocialAccountStatus::PENDING_VALIDATION->value) {
            return;
        }

        $this->messageBus->dispatch(new RemoveSocialAccount(socialAccountId: $youtubeAccount->getId(), status: SocialAccountStatus::PENDING_VALIDATION), [
            new DelayStamp(360000000),
            new AmqpStamp('async-low'),
        ]);
    }
}
