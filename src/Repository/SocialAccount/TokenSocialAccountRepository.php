<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\TokenSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<TokenSocialAccount>
 */
class TokenSocialAccountRepository extends AbstractRepository implements SocialAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TokenSocialAccount::class);
    }
}
