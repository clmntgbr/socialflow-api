<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\YoutubeSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<YoutubeSocialAccount>
 */
class YoutubeSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YoutubeSocialAccount::class);
    }
}
