<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\Post;
use App\Entity\Post\TwitterPost;

class TwitterPublishService implements PublishServiceInterface
{
    /**
     * @param TwitterPost $post
     */
    public function post(Post $post): GetPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function delete()
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
