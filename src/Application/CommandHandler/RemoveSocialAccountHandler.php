<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrganization;
use App\Application\Command\RemoveSocialAccount;
use App\Entity\Organization;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use App\Repository\SocialAccount\SocialAccountRepository;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class RemoveSocialAccountHandler
{
    public function __construct(
        private SocialAccountRepository $socialAccountRepository,
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
            throw new \Exception(sprintf('Social account does not exist with id [%s]', (string) $message->socialAccountId));
        }

        $this->socialAccountRepository->delete($socialAccount);
    }
}
