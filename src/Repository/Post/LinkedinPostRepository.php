<?php

namespace App\Repository\Post;

use App\Entity\Post\LinkedinPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<LinkedinPost>
 */
class LinkedinPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, LinkedinPost::class);
    }
}
