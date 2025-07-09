<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\InstagramPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: InstagramPostRepository::class)]
#[ApiResource(
    operations: []
)]
class InstagramPost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
