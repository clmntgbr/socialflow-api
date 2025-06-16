<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\YoutubeSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: YoutubeSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class YoutubeSocialAccount extends SocialAccount
{
}
