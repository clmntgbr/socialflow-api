<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkedinSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinSocialAccount extends SocialAccount
{
}
