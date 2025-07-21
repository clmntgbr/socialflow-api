<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\ApiResource\PatchUserActiveGroupController;
use App\ApiResource\PatchUserController;
use App\ApiResource\PatchUserNameController;
use App\Dto\User\PatchUserActiveGroup;
use App\Dto\User\PatchUserName;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\Trait\UuidTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Groups;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ApiResource(
    operations: [
        new Get(
            uriTemplate: '/me',
        ),
        new Patch(
            uriTemplate: '/me/name',
            controller: PatchUserNameController::class,
        ),
        new Patch(
            uriTemplate: '/me/active-group',
            controller: PatchUserActiveGroupController::class,
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(length: 180)]
    #[Groups(['user.read', 'group.read.full'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['user.read', 'group.read.full'])]
    private string $firstname;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['user.read', 'group.read.full'])]
    private string $lastname;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user.read', 'group.read.full'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $state;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user.read'])]
    private ?Group $activeGroup = null;

    #[ORM\ManyToMany(targetEntity: Group::class, mappedBy: 'members', cascade: ['persist', 'remove'])]
    private Collection $groups;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->groups = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[Groups(['user.read', 'group.read.full'])]
    public function getName(): ?string
    {
        return $this->firstname.' '.$this->lastname;
    }

    public function setEmail(string $email): static
    {
        if (null !== $this->email && $this->email !== $email) {
            return $this;
        }

        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    public function setPlainPassword(string $password): static
    {
        $this->plainPassword = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getState(): ?string
    {
        return $this->state;
    }

    public function setState(?string $state): static
    {
        $this->state = $state;

        return $this;
    }

    public function getActiveGroup(): ?Group
    {
        return $this->activeGroup;
    }

    public function setActiveGroup(?Group $activeGroup): static
    {
        $this->activeGroup = $activeGroup;

        return $this;
    }

    /**
     * @return Collection<int, Group>
     */
    public function getGroups(): Collection
    {
        return $this->groups;
    }

    public function isMemberOfGroup(?Group $group): bool
    {
        if (null === $group) {
            return false;
        }

        return $this->groups->contains($group);
    }

    public function addGroup(Group $group): static
    {
        if (!$this->groups->contains($group)) {
            $this->groups->add($group);
            $group->addMember($this);
        }

        return $this;
    }

    public function removeGroup(Group $group): static
    {
        if ($this->groups->removeElement($group)) {
            $group->removeMember($this);
        }

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function isSocialAccountInActiveGroup(SocialAccount $socialAccount): bool
    {
        if (null === $this->activeGroup || null === $socialAccount->getGroup()) {
            return false;
        }

        return $socialAccount->getGroup()->getId() === $this->activeGroup->getId();
    }
}