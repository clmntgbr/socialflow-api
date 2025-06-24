<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Doctrine\Orm\Filter\SearchFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\Organization;
use App\Entity\Trait\UuidTrait;
use App\Entity\ValueObject\SocialAccountStatus;
use App\Enum\SocialAccountStatus as EnumSocialAccountStatus;
use App\Repository\SocialAccount\SocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Embedded;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SocialAccountRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'linkedin_social_account' => 'LinkedinSocialAccount',
    'twitter_social_account' => 'TwitterSocialAccount',
    'facebook_social_account' => 'FacebookSocialAccount',
    'youtube_social_account' => 'YoutubeSocialAccount',
    'thread_social_account' => 'ThreadSocialAccount',
    'instagram_social_account' => 'InstagramSocialAccount',
])]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['social_account.read']],
        ),
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'status.value' => 'exact',
    ]
)]
class SocialAccount
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $socialAccountId;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $username;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $avatarUrl = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $token = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $refreshToken = null;

    #[ORM\Column(type: Types::BOOLEAN)]
    #[Groups(['social_account.read'])]
    private bool $isVerified = false;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $followers = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $followings = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $likes = 0;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $website = null;

    #[Embedded(class: SocialAccountStatus::class, columnPrefix: false)]
    #[Groups(['social_account.read'])]
    private SocialAccountStatus $status;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'socialAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Organization $organization = null;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->status = new SocialAccountStatus(value: EnumSocialAccountStatus::PENDING_VALIDATION->getValue());
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

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getAvatarUrl(): ?string
    {
        return $this->avatarUrl;
    }

    public function setAvatarUrl(?string $avatarUrl): static
    {
        $this->avatarUrl = $avatarUrl;

        return $this;
    }

    public function getAccessToken(): ?string
    {
        return $this->token;
    }

    public function setToken(?string $token): static
    {
        $this->token = $token;

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

    public function isVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function setOrganization(?Organization $organization): static
    {
        $this->organization = $organization;

        return $this;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function getStatus(): SocialAccountStatus
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = new SocialAccountStatus(value: $status);

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }

    public function getFollowers(): ?int
    {
        return $this->followers;
    }

    public function setFollowers(int $followers): static
    {
        $this->followers = $followers;

        return $this;
    }

    public function getFollowings(): ?int
    {
        return $this->followings;
    }

    public function setFollowings(int $followings): static
    {
        $this->followings = $followings;

        return $this;
    }

    public function getLikes(): ?int
    {
        return $this->likes;
    }

    public function setLikes(int $likes): static
    {
        $this->likes = $likes;

        return $this;
    }
}
