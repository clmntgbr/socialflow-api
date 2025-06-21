<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LinkedinSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinSocialAccount extends SocialAccount
{
    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'linkedin';
    }
}
