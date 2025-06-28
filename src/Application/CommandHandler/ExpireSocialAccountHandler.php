<?php

namespace App\Application\CommandHandler;

use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\SocialAccount\SocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\SocialAccount\SocialAccountRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ExpireSocialAccountHandler
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(ExpireSocialAccount $message): void
    {
        /** @var ?SocialAccount $socialAccount */
        $socialAccount = $this->socialAccountRepository->findOneBy([
            'id' => (string) $message->id,
        ]);

        if (null === $socialAccount) {
            $this->logger->warning(sprintf('Social account does not exist with id [%s]', (string) $message->id));

            return;
        }

        $socialAccount->setStatus(SocialAccountStatus::EXPIRED->value);
        $this->socialAccountRepository->save($socialAccount);
    }
}
