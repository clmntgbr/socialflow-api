<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\FacebookPostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Groups;

#[ORM\Entity(repositoryClass: FacebookPostRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookPost extends Post implements PostInterface
{
    #[Groups(['post.read'])]
    public function getType(): string
    {
        return 'facebook';
    }

    public function __construct()
    {
        parent::__construct();
    }
}
