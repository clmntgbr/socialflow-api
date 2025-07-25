<?php

namespace App\Entity\Post;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\Post\LinkedinPostRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: LinkedinPostRepository::class)]
#[ApiResource(
    operations: []
)]
class LinkedinPost extends Post implements PostInterface
{
    public function __construct()
    {
        parent::__construct();
    }
}
