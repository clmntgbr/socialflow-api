<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\FacebookSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: FacebookSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookSocialAccount extends SocialAccount
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $link = null;

    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'facebook_social_account';
    }

    public function __construct(Uuid $uuid)
    {
        parent::__construct();
        $this->setId($uuid);
    }

    public function getLink(): ?string
    {
        return $this->link;
    }

    public function setLink(?string $link): static
    {
        $this->link = $link;

        return $this;
    }
}
