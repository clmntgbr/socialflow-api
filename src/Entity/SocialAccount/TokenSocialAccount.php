<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Organization;
use App\Entity\Post\Cluster;
use App\Entity\Trait\UuidTrait;
use App\Enum\SocialAccountStatus;
use App\Repository\SocialAccount\SocialAccountRepository;
use App\Repository\SocialAccount\TokenSocialAccountRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TokenSocialAccountRepository::class)]
class TokenSocialAccount
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read', 'organization.read.full'])]
    private string $socialAccountId;
    
    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $tokenSecret = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['social_account.read', 'organization.read.full'])]
    private ?\DateTime $expireAt = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

        return $this;
    }

    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTime $expireAt): static
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getRefreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(?string $refreshToken): static
    {
        if (null === $refreshToken) {
            return $this;
        }

        $this->refreshToken = $refreshToken;

        return $this;
    }

    public function setRefreshTokenAndExpireAt(?string $refreshToken, \DateTime $expireAt): static
    {
        if (null === $refreshToken) {
            return $this;
        }

        $this->refreshToken = $refreshToken;
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getTokenSecret(): ?string
    {
        return $this->tokenSecret;
    }

    public function setTokenSecret(?string $tokenSecret): static
    {
        $this->tokenSecret = $tokenSecret;

        return $this;
    }

    public function getSocialAccountId(): ?string
    {
        return $this->socialAccountId;
    }

    public function setSocialAccountId(string $socialAccountId): static
    {
        $this->socialAccountId = $socialAccountId;

        return $this;
    }
}
