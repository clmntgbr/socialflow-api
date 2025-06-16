<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\YoutubeSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<YoutubeSocialAccount>
 */
class YoutubeSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YoutubeSocialAccount::class);
    }
}
