<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\ThreadSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class ThreadSocialAccount extends SocialAccount
{
}
