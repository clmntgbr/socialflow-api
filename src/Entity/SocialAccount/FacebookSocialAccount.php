<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Dto\SocialAccount\Restrictions\FacebookRestrictions;
use App\Dto\SocialAccount\Restrictions\RestrictionInterface;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FacebookSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookSocialAccount extends SocialAccount implements SocialAccountInterface
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $link = null;

    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'facebook';
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): self
    {
        $this->link = $link;

        return $this;
    }

    #[Groups(['social_account.read'])]
    public function getRestrictions(): RestrictionInterface
    {
        return new FacebookRestrictions($this);
    }
}
