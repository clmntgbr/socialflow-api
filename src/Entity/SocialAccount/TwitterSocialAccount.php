<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Dto\SocialAccount\Restrictions\RestrictionInterface;
use App\Dto\SocialAccount\Restrictions\TwitterRestrictionNotVerified;
use App\Dto\SocialAccount\Restrictions\TwitterRestrictions;
use App\Dto\SocialAccount\Restrictions\TwitterRestrictionVerified;
use App\Repository\SocialAccount\TwitterSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TwitterSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterSocialAccount extends SocialAccount implements SocialAccountInterface
{
    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $tokenSecret = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $tweets = 0;

    public function __construct()
    {
        parent::__construct();
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

    #[Groups(['social_account.read'])]
    public function getRestrictions(): RestrictionInterface
    {
        return new TwitterRestrictions($this);
    }
}
