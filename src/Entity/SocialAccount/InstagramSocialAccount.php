<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\InstagramSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstagramSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class InstagramSocialAccount extends SocialAccount
{
}
