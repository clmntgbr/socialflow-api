<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\YoutubePostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: YoutubePostRepository::class)]
#[ApiResource(
    operations: []
)]
class YoutubePost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
