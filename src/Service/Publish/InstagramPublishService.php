<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\InstagramPost;
use App\Entity\Post\Post;

class InstagramPublishService implements PublishServiceInterface
{
    /**
     * @param InstagramPost $post
     */
    public function post(Post $post): GetPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /** @param InstagramPost $post */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
