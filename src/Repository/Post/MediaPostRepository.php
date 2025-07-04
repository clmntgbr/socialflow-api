<?php

namespace App\Repository\Post;

use App\Entity\Post\MediaPost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<MediaPost>
 */
class MediaPostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, MediaPost::class);
    }
}
