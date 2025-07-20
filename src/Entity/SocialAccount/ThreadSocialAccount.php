<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\ThreadSocialAccountRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ThreadSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class ThreadSocialAccount extends SocialAccount implements SocialAccountInterface
{
    public function getType(): string
    {
        return 'thread';
    }
}
