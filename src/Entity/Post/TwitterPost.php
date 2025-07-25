<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\TwitterPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TwitterPostRepository::class)]
#[ApiResource(
    operations: []
)]
class TwitterPost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
