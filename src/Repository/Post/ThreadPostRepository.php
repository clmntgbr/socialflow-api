<?php

namespace App\Repository\Post;

use App\Entity\Post\ThreadPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<ThreadPost>
 */
class ThreadPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ThreadPost::class);
    }
}
