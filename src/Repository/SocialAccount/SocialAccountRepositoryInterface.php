<?php

namespace App\Repository\SocialAccount;

interface SocialAccountRepositoryInterface
{
    public function findOneBy(array $criteria, ?array $orderBy = null): mixed;
}
