<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrganization;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Repository\SocialAccount\SocialAccountRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoveSocialAccountHandler
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
        private LoggerInterface $logger
    ) {
    }

    public function __invoke(RemoveSocialAccount $message): void
    {
        /** @var ?SocialAccount $socialAccount */
        $socialAccount = $this->socialAccountRepository->findOneBy([
            'id' => (string) $message->socialAccountId,
            'status.value' => $message->status->getValue(),
        ]);

        if (null === $socialAccount) {
            $this->logger->warning(sprintf('Social account does not exist with id [%s] and status [%s]', (string) $message->socialAccountId, $message->status->getValue()));
            return;
        }

        $this->socialAccountRepository->delete($socialAccount);
    }
}
