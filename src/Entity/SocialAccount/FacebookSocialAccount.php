<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacebookSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookSocialAccount extends SocialAccount
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }
}
