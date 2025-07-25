<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<LinkedinSocialAccount>
 */
class LinkedinSocialAccountRepository extends AbstractRepository implements SocialAccountRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkedinSocialAccount::class);
    }
}
