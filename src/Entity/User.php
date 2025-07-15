<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Patch;
use App\ApiResource\PatchUserController;
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
            normalizationContext: ['groups' => ['user.read']],
        ),
        new Patch(
            uriTemplate: '/me',
            controller: PatchUserController::class,
            normalizationContext: ['groups' => ['user.read']],
            denormalizationContext: ['groups' => ['user.write']],
        ),
    ]
)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\Column(length: 180)]
    #[Groups(['user.read', 'organization.read.full'])]
    private ?string $email = null;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['user.read', 'organization.read.full', 'user.write'])]
    private string $firstname;

    #[ORM\Column(type: Types::STRING)]
    #[Groups(['user.read', 'organization.read.full', 'user.write'])]
    private string $lastname;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Groups(['user.read', 'organization.read.full'])]
    private array $roles = [];

    #[ORM\Column]
    private ?string $password = null;

    private ?string $plainPassword = null;

    #[ORM\Column(type: Types::STRING, nullable: true)]
    private ?string $state;

    #[ORM\ManyToOne(targetEntity: Organization::class)]
    #[ORM\JoinColumn(nullable: true)]
    #[Groups(['user.read', 'user.write'])]
    private ?Organization $activeOrganization = null;

    #[ORM\ManyToMany(targetEntity: Organization::class, mappedBy: 'members', cascade: ['persist', 'remove'])]
    private Collection $organizations;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->organizations = new ArrayCollection();
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    #[Groups(['user.read', 'organization.read.full'])]
    public function getName(): ?string
    {
        return $this->firstname . ' ' . $this->lastname;
    }

    public function setEmail(string $email): static
    {
        if ($this->email !== null && $this->email !== $email) {
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

    public function getActiveOrganization(): ?Organization
    {
        return $this->activeOrganization;
    }

    public function setActiveOrganization(?Organization $activeOrganization): static
    {
        $this->activeOrganization = $activeOrganization;

        return $this;
    }

    /**
     * @return Collection<int, Organization>
     */
    public function getOrganizations(): Collection
    {
        return $this->organizations;
    }

    public function isMemberOfOrganization(?Organization $organization): bool
    {
        if ($organization === null) {
            return false;
        }

        return $this->organizations->contains($organization);
    }

    public function addOrganization(Organization $organization): static
    {
        if (!$this->organizations->contains($organization)) {
            $this->organizations->add($organization);
            $organization->addMember($this);
        }

        return $this;
    }

    public function removeOrganization(Organization $organization): static
    {
        if ($this->organizations->removeElement($organization)) {
            $organization->removeMember($this);
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
}
