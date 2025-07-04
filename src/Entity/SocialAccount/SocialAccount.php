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
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: SocialAccountRepository::class)]
#[ORM\InheritanceType('SINGLE_TABLE')]
#[ORM\DiscriminatorColumn(name: 'type', type: 'string')]
#[ORM\DiscriminatorMap([
    'linkedin' => 'LinkedinSocialAccount',
    'twitter' => 'TwitterSocialAccount',
    'facebook' => 'FacebookSocialAccount',
    'youtube' => 'YoutubeSocialAccount',
    'thread' => 'ThreadSocialAccount',
    'instagram' => 'InstagramSocialAccount',
])]
#[ApiResource(
    order: ['updatedAt' => 'DESC'],
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['social_account.read']],
        ),
        new Get(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['social_account.read']],
        ),
        new Delete(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['social_account.read']],
        ),
    ]
)]
#[ApiFilter(
    SearchFilter::class,
    properties: [
        'id' => 'exact',
        'status' => 'exact',
        'type' => 'exact',
    ]
)]
class SocialAccount implements SocialAccountInterface
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

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?\DateTime $expireAt = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $status;

    #[ORM\ManyToOne(targetEntity: Organization::class, inversedBy: 'socialAccounts')]
    #[ORM\JoinColumn(nullable: false)]
    private Organization $organization;

    #[ORM\OneToMany(targetEntity: Cluster::class, mappedBy: 'socialAccount', cascade: ['remove'])]
    private Collection $clusters;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->status = SocialAccountStatus::PENDING_VALIDATION->value;
        $this->clusters = new ArrayCollection();
    }

    public function getType(): string
    {
        return '';
    }

    public function isActive(): bool
    {
        return $this->status === SocialAccountStatus::ACTIVE->value;
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

    public function getToken(): ?string
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

    public function setRefreshTokenAndExpireAt(?string $refreshToken, \DateTime $expireAt): static
    {
        if (null === $refreshToken) {
            return $this;
        }

        $this->refreshToken = $refreshToken;
        $this->expireAt = $expireAt;

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

    public function getExpireAt(): ?\DateTime
    {
        return $this->expireAt;
    }

    public function setExpireAt(?\DateTime $expireAt): static
    {
        $this->expireAt = $expireAt;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    /**
     * @return Collection<int, Cluster>
     */
    public function getClusters(): Collection
    {
        return $this->clusters;
    }

    public function addCluster(Cluster $cluster): static
    {
        if (!$this->clusters->contains($cluster)) {
            $this->clusters->add($cluster);
            $cluster->setSocialAccount($this);
        }

        return $this;
    }

    public function removeCluster(Cluster $cluster): static
    {
        if ($this->clusters->removeElement($cluster)) {
            // set the owning side to null (unless already changed)
            if ($cluster->getSocialAccount() === $this) {
                $cluster->setSocialAccount(null);
            }
        }

        return $this;
    }
}
