<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<LinkedinSocialAccount>
 */
class LinkedinSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkedinSocialAccount::class);
    }
}
