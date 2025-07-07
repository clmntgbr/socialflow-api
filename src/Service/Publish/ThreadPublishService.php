<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\Post;
use App\Entity\Post\ThreadPost;

class ThreadPublishService implements PublishServiceInterface
{
    /**
     * @param ThreadPost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * @param ThreadPost $post
     */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * @param ThreadPost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
