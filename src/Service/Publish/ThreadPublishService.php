<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\Post;
use App\Entity\Post\ThreadPost;

class ThreadPublishService implements PublishServiceInterface
{
    /**
     * @param ThreadPost $post
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
