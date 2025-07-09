<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\ThreadPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ThreadPostRepository::class)]
#[ApiResource(
    operations: []
)]
class ThreadPost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
