<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\Post;
use App\Entity\Post\YoutubePost;

class YoutubePublishService implements PublishServiceInterface
{
    /**
     * @param YoutubePost $post
     */
    public function post(Post $post): GetPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /** @param YoutubePost $post */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
