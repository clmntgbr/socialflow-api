<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
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
    public function getRestrictions(): array
    {
        if ($this->isVerified()) {
            return $this->getRestrictionVerified();
        }
        
        return $this->getRestrictionNotVerified();
    }

    private function getRestrictionVerified()
    {
        return [
            'text' => [
                'max_characters' => 25000,
            ],
            'video' => [
                'max_duration_seconds' => 7200,
                'max_file_size_bytes' => 8589934592,
                'max_file_size_formatted' => '8 GB',
            ],
            'image' => [
                'max_file_size_bytes' => 5242880,
                'max_file_size_formatted' => '5 MB',
            ],
        ];
    }

    private function getRestrictionNotVerified()
    {
        return [
            'text' => [
                'max_characters' => 280,
            ],
            'video' => [
                'max_duration_seconds' => 140,
                'max_file_size_bytes' => 536870912,
                'max_file_size_formatted' => '512 MB',
            ],
            'image' => [
                'max_file_size_bytes' => 5242880,
                'max_file_size_formatted' => '5 MB',
            ],
        ];
    }
}
