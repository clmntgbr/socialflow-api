<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<FacebookSocialAccount>
 */
class FacebookSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookSocialAccount::class);
    }
}
