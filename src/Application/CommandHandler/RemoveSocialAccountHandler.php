<?php

namespace App\Application\CommandHandler;

use App\Application\Command\RemoveSocialAccount;
use App\Entity\SocialAccount\SocialAccount;
use App\Repository\SocialAccount\SocialAccountRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoveSocialAccountHandler
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(RemoveSocialAccount $message): void
    {
        /** @var ?SocialAccount $socialAccount */
        $socialAccount = $this->socialAccountRepository->findOneBy([
            'id' => (string) $message->socialAccountId,
            'status' => $message->status->value,
        ]);

        if (null === $socialAccount) {
            $this->logger->warning(sprintf('Failed to remove social account: account with id [%s] and status [%s] was not found.', (string) $message->socialAccountId, $message->status->value), ['id' => (string) $message->socialAccountId, 'status' => $message->status->value]);

            return;
        }

        $this->socialAccountRepository->delete($socialAccount);
    }
}
