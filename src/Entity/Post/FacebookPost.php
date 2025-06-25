<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\FacebookPostRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FacebookPostRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookPost extends Post implements PostInterface
{
    #[ORM\Column(type: Types::STRING)]
    #[Groups(['cluster.read', 'post.read', 'post.write'])]
    private string $test;
    
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'facebook';
    }

    public function __construct()
    {
        parent::__construct();
    }

    public function getTest(): ?string
    {
        return $this->test;
    }

    public function setTest(string $test): static
    {
        $this->test = $test;

        return $this;
    }
}
