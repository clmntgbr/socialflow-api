<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\Post;
use App\Entity\Post\ThreadPost;
use App\Exception\MethodNotImplementedException;

class ThreadPublishService implements PublishServiceInterface
{
    /**
     * @param ThreadPost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param ThreadPost $post
     */
    public function delete(Post $post): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param ThreadPost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function upload(UploadPayloadInterface $uploadPayloadInterface): UploadedMediaIdInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
