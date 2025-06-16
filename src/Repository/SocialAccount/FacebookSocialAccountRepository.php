<?php

namespace App\Repository\SocialAccount;

use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<FacebookSocialAccount>
 */
class FacebookSocialAccountRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookSocialAccount::class);
    }
}
