<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\TwitterPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: TwitterPostRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterPost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'twitter';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
