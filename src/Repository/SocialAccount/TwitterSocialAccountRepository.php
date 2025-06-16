<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<TwitterSocialAccount>
 */
class TwitterSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitterSocialAccount::class);
    }
}
