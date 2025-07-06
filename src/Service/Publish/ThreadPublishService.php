<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\PublishedPostInterface;
use App\Entity\Post\Post;
use App\Entity\Post\ThreadPost;

class ThreadPublishService implements PublishServiceInterface
{
    /**
     * @param ThreadPost $post
     */
    public function post(Post $post): PublishedPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /** @param ThreadPost $post */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
