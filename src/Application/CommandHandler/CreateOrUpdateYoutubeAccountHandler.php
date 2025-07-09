<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrUpdateYoutubeAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\YoutubeSocialAccount;
use App\Enum\SocialAccountStatus;
use App\Exception\OrganizationNotFoundException;
use App\Repository\OrganizationRepository;
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
        private OrganizationRepository $organizationRepository,
        private YoutubeSocialAccountRepository $youtubeSocialAccountRepository,
        private MessageBusInterface $messageBus,
    ) {
        parent::__construct($youtubeSocialAccountRepository);
    }

    public function __invoke(CreateOrUpdateYoutubeAccount $message): void
    {
        /** @var ?Organization $organization */
        $organization = $this->organizationRepository->findOneBy(['id' => (string) $message->organizationId]);

        if (null === $organization) {
            throw new OrganizationNotFoundException((string) $message->organizationId);
        }

        /** @var YoutubeSocialAccount $youtubeAccount */
        $youtubeAccount = $this->getAccount(
            socialAccountId: $message->youtubeAccount->id,
            organization: $organization, class: YoutubeSocialAccount::class
        );

        $date = new \DateTime();
        $date->modify(sprintf('+%s seconds', $message->youtubeToken->expiresIn));

        $youtubeAccount
            ->setDescription($message->youtubeAccount->description)
            ->setName($message->youtubeAccount->name)
            ->setUsername($message->youtubeAccount->username)
            ->setSocialAccountId($message->youtubeAccount->id)
            ->setOrganization($organization)
            ->setFollowers($message->youtubeAccount->publicMetrics->followers)
            ->setAvatarUrl($message->youtubeAccount->picture)
            ->setIsVerified($message->youtubeAccount->verified)
            ->setToken($message->youtubeToken->token)
            ->setRefreshTokenAndExpireAt($message->youtubeToken->refreshToken, $date);

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
