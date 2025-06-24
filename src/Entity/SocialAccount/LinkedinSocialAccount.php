<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LinkedinSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinSocialAccount extends SocialAccount
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $name;

    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'linkedin';
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }
}
