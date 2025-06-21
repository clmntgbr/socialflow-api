<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\YoutubeSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: YoutubeSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class YoutubeSocialAccount extends SocialAccount
{
    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'youtube';
    }
}
