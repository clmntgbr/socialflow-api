<?php

namespace App\Repository\Post;

use App\Entity\Post\TwitterPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<TwitterPost>
 */
class TwitterPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TwitterPost::class);
    }
}
