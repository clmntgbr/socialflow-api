<?php

namespace App\Entity\SocialAccount;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\SocialAccount\YoutubeSocialAccountRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: YoutubeSocialAccountRepository::class)]
#[ApiResource(
    operations: []
)]
class YoutubeSocialAccount extends SocialAccount implements SocialAccountInterface
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['social_account.read'])]
    private string $name;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $views = 0;

    #[ORM\Column(type: Types::INTEGER)]
    #[Groups(['social_account.read'])]
    private int $videos = 0;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    #[Groups(['social_account.read'])]
    private ?string $description = null;

    public function getType(): string
    {
        return 'youtube';
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

    public function getViews(): ?int
    {
        return $this->views;
    }

    public function setViews(int $views): static
    {
        $this->views = $views;

        return $this;
    }

    public function getVideos(): ?int
    {
        return $this->videos;
    }

    public function setVideos(int $videos): static
    {
        $this->videos = $videos;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }
}
