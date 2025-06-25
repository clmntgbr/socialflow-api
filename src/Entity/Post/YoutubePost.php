<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\YoutubePostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: YoutubePostRepository::class)]
#[ApiResource(
    operations: []
)]
class YoutubePost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'youtube';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
