<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\GetCollection;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\Trait\UuidTrait;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ApiResource(
    order: ['updatedAt' => 'DESC'],
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['organization.read']],
        )
    ]
)]
class Organization
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['organization.read', 'organization.read.full'])]
    private string $name;

    #[ORM\OneToMany(targetEntity: SocialAccount::class, mappedBy: 'organization', cascade: ['remove'])]
    #[Groups(['organization.read.full'])]
    private Collection $socialAccounts;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organizations')]
    #[Groups(['organization.read.full'])]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn()]
    #[Groups(['organization.read.full'])]
    private User $admin;

    #[Groups(['organization.read', 'organization.read.full'])]
    private string $role = 'member';

    #[Groups(['organization.read', 'organization.read.full'])]
    private bool $isAdmin = false;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->socialAccounts = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    #[Groups(['organization.read', 'organization.read.full'])]
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    #[Groups(['organization.read', 'organization.read.full'])]
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function getRole(): string
    {
        return $this->role;
    }

    public function markAsAdmin()
    {
        $this->role = 'admin';
        $this->isAdmin = true;
    }

    public function getIsAdmin(): bool
    {
        return $this->isAdmin;
    }

    /**
     * @return Collection<int, SocialAccount>
     */
    public function getSocialAccounts(): Collection
    {
        return $this->socialAccounts;
    }

    public function addSocialAccount(SocialAccount $socialAccount): static
    {
        if (!$this->socialAccounts->contains($socialAccount)) {
            $this->socialAccounts->add($socialAccount);
            $socialAccount->setOrganization($this);
        }

        return $this;
    }

    public function removeSocialAccount(SocialAccount $socialAccount): static
    {
        if ($this->socialAccounts->removeElement($socialAccount)) {
            // set the owning side to null (unless already changed)
            if ($socialAccount->getOrganization() === $this) {
                $socialAccount->setOrganization(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, User>
     */
    public function getMembers(): Collection
    {
        return $this->members;
    }

    public function addMember(User $user): static
    {
        if (!$this->members->contains($user)) {
            $this->members->add($user);
        }

        return $this;
    }

    public function removeMember(User $user): static
    {
        $this->members->removeElement($user);

        return $this;
    }

    public function getAdmin(): ?User
    {
        return $this->admin;
    }

    public function setAdmin(?User $admin): static
    {
        $this->admin = $admin;

        return $this;
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
