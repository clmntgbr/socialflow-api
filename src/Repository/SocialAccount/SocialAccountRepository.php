<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\SocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<SocialAccount>
 */
class SocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SocialAccount::class);
    }
}
