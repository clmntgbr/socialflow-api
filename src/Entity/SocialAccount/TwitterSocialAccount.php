<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TwitterSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterSocialAccount extends SocialAccount
{
    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'twitter_social_account';
    }
}
