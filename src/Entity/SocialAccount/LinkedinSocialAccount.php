<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Dto\SocialAccount\Restrictions\LinkedinRestrictions;
use App\Dto\SocialAccount\Restrictions\RestrictionInterface;
use App\Repository\SocialAccount\LinkedinSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LinkedinSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinSocialAccount extends SocialAccount implements SocialAccountInterface
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $name;

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

    public function getRestrictions(): RestrictionInterface
    {
        return new LinkedinRestrictions($this);
    }
}
