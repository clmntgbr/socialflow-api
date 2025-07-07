<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\InstagramPost;
use App\Entity\Post\Post;

class InstagramPublishService implements PublishServiceInterface
{
    /**
     * @param InstagramPost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * @param InstagramPost $post
     */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    /**
     * @param InstagramPost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
