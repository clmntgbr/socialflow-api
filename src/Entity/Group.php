<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use App\ApiResource\GetActiveGroupController;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\Trait\UuidTrait;
use App\Repository\GroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Table(name: '`group`')]
#[ORM\Entity(repositoryClass: GroupRepository::class)]
#[ApiResource(
    order: ['updatedAt' => 'DESC'],
    operations: [
        new GetCollection(
            normalizationContext: ['skip_null_values' => false, 'groups' => ['group.read']],
        ),
        new Get(
            uriTemplate: '/group',
            controller: GetActiveGroupController::class,
            normalizationContext: ['groups' => ['group.read']],
        ),
    ]
)]
class Group
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['group.read', 'group.read.full'])]
    private string $name;

    #[ORM\OneToMany(targetEntity: SocialAccount::class, mappedBy: 'group', cascade: ['remove'])]
    #[Groups(['group.read.full'])]
    private Collection $socialAccounts;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'groups')]
    #[Groups(['group.read.full'])]
    private Collection $members;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn()]
    #[Groups(['group.read.full'])]
    private User $admin;

    #[Groups(['group.read', 'group.read.full'])]
    private string $role = 'member';

    #[Groups(['group.read', 'group.read.full'])]
    private bool $isAdmin = false;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->socialAccounts = new ArrayCollection();
        $this->members = new ArrayCollection();
    }

    #[Groups(['group.read', 'group.read.full'])]
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    #[Groups(['group.read', 'group.read.full'])]
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
            $socialAccount->setGroup($this);
        }

        return $this;
    }

    public function removeSocialAccount(SocialAccount $socialAccount): static
    {
        if ($this->socialAccounts->removeElement($socialAccount)) {
            // set the owning side to null (unless already changed)
            if ($socialAccount->getGroup() === $this) {
                $socialAccount->setGroup(null);
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
