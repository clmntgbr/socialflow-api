<?php

namespace App\Repository\Post;

use App\Entity\Post\YoutubePost;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<YoutubePost>
 */
class YoutubePostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, YoutubePost::class);
    }
}
