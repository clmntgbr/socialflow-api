<?php

namespace App\Application\CommandHandler;

use App\Entity\Organization;
use App\Entity\SocialAccount\SocialAccount;
use App\Enum\SocialAccountStatus;
use App\Repository\SocialAccount\SocialAccountRepositoryInterface;

abstract class CreateOrUpdateAccountHandlerAbstract
{
    public function __construct(
        private SocialAccountRepositoryInterface $socialAccountRepository,
    ) {
    }

    public function getAccount(string $socialAccountId, Organization $organization, string $class): SocialAccount
    {
        /** @var ?SocialAccount $account */
        $account = $this->socialAccountRepository->findOneBy([
            'organization' => $organization,
            'socialAccountId' => $socialAccountId,
        ]);

        if (null === $account) {
            return new $class();
        }

        if ($account->getStatus() === SocialAccountStatus::EXPIRED->value) {
            $account->setStatus(SocialAccountStatus::ACTIVE->value);
        }

        return $account;
    }
}
