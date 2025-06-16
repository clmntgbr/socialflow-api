<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TwitterSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterSocialAccount extends SocialAccount
{
}
