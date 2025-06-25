<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\InstagramPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: InstagramPostRepository::class)]
#[ApiResource(
    operations: []
)]
class InstagramPost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'instagram';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
