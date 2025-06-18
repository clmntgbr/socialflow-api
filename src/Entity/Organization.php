<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\Trait\UuidTrait;
use App\Repository\OrganizationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity(repositoryClass: OrganizationRepository::class)]
#[ApiResource]
class Organization
{
    use UuidTrait;
    use TimestampableEntity;

    #[ORM\OneToMany(targetEntity: SocialAccount::class, mappedBy: 'organization', cascade: ['remove'])]
    private Collection $socialAccounts;

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'organizations')]
    private Collection $users;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn()]
    private User $admin;

    public function __construct()
    {
        $this->id = Uuid::v4();
        $this->socialAccounts = new ArrayCollection();
        $this->users = new ArrayCollection();
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
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        $this->users->removeElement($user);

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
}
