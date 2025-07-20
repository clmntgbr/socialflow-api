<?php

namespace App\Application\CommandHandler;

use App\Application\Command\SocialAccountOnActivation;
use App\Entity\SocialAccount\SocialAccount;
use App\Repository\SocialAccount\SocialAccountRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class SocialAccountOnActivationHandler
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(SocialAccountOnActivation $message): void
    {
        /** @var ?SocialAccount $socialAccount */
        $socialAccount = $this->socialAccountRepository->findOneBy([
            'id' => (string) $message->socialAccountId,
        ]);

        if (null === $socialAccount) {
            $this->logger->warning(sprintf('Failed to process social account activation: account with id [%s] was not found.', (string) $message->socialAccountId), ['id' => (string) $message->socialAccountId]);

            return;
        }

        $this->logger->info(sprintf('Processing activation for social account with id [%s] of type [%s]', (string) $socialAccount->getId(), $socialAccount->getType()), [
            'id' => (string) $socialAccount->getId(),
            'type' => $socialAccount->getType(),
            'username' => $socialAccount->getUsername(),
        ]);
    }
} 