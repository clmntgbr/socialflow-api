<?php

namespace App\Repository\Post;

use App\Entity\Post\Post;
use App\Repository\AbstractRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends AbstractRepository<Post>
 */
class PostRepository extends AbstractRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Post::class);
    }

    public function getPreviousPost(Post $post): ?Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.cluster = :cluster')
            ->andWhere('p.order < :currentOrder')
            ->andWhere('p.id != :currentId')
            ->setParameter('cluster', $post->getCluster())
            ->setParameter('currentOrder', $post->getOrder())
            ->setParameter('currentId', $post->getId())
            ->orderBy('p.order', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function getNextPost(Post $post): ?Post
    {
        return $this->createQueryBuilder('p')
            ->where('p.cluster = :cluster')
            ->andWhere('p.order > :currentOrder')
            ->andWhere('p.id != :currentId')
            ->setParameter('cluster', $post->getCluster())
            ->setParameter('currentOrder', $post->getOrder())
            ->setParameter('currentId', $post->getId())
            ->orderBy('p.order', 'ASC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
