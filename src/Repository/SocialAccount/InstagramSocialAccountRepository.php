<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\InstagramSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<InstagramSocialAccount>
 */
class InstagramSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstagramSocialAccount::class);
    }
}
