<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\ThreadPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: ThreadPostRepository::class)]
#[ApiResource(
    operations: []
)]
class ThreadPost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'thread';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
