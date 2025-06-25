<?php

namespace App\Repository\Post;

use App\Entity\Post\InstagramPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<InstagramPost>
 */
class InstagramPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, InstagramPost::class);
    }
}
