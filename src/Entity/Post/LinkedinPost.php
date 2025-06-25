<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\LinkedinPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: LinkedinPostRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinPost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'linkedin';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
