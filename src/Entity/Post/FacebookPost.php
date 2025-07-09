<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\FacebookPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: FacebookPostRepository::class)]
#[ApiResource(
    operations: []
)]
class FacebookPost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
