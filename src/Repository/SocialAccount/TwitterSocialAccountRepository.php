<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TwitterSocialAccount>
 */
class TwitterSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitterSocialAccount::class);
    }
}
