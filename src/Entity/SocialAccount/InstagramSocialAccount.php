<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\InstagramSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InstagramSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class InstagramSocialAccount extends SocialAccount
{
    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'instagram';
    }
}
