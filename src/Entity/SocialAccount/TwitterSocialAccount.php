<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: TwitterSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterSocialAccount extends SocialAccount
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $tokenSecret = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $tweets = 0;

    public function __construct(Uuid $uuid)
    {
        parent::__construct();
        $this->setId($uuid);
    }

    #[Groups(['social_account.read'])]
    public function getType(): string
    {
        return 'twitter';
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

    public function getTokenSecret(): ?string
    {
        return $this->tokenSecret;
    }

    public function setTokenSecret(?string $tokenSecret): static
    {
        $this->tokenSecret = $tokenSecret;

        return $this;
    }

    public function getTweets(): ?int
    {
        return $this->tweets;
    }

    public function setTweets(int $tweets): static
    {
        $this->tweets = $tweets;

        return $this;
    }
}
