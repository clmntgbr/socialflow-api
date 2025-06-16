<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\ThreadSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<ThreadSocialAccount>
 */
class ThreadSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadSocialAccount::class);
    }
}
