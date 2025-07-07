<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Entity\Post\YoutubePost;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\SocialAccount\YoutubeSocialAccount;
use App\Exception\MethodNotImplementedException;

class YoutubePublishService implements PublishServiceInterface
{
    /**
     * @param YoutubePost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param YoutubePost $post
     */
    public function delete(Post $post): void
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param YoutubePost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /** 
     * @param YoutubeSocialAccount $socialAccount
     */
    public function upload(UploadPayloadInterface $uploadPayloadInterface): UploadedMediaIdInterface
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
