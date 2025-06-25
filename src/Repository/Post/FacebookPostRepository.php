<?php

namespace App\Repository\Post;

use App\Entity\Post\FacebookPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<FacebookPost>
 */
class FacebookPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, FacebookPost::class);
    }
}
