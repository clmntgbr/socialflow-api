<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\InstagramPost;
use App\Entity\Post\Post;
use App\Exception\MethodNotImplementedException;

class InstagramPublishService implements PublishServiceInterface
{
    /**
     * @param InstagramPost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param InstagramPost $post
     */
    public function delete(Post $post): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param InstagramPost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
