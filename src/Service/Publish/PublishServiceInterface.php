<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Entity\SocialAccount\SocialAccount;

interface PublishServiceInterface
{
    public const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const VIDEO_MIME_TYPES = ['video/mp4', 'video/avi', 'video/mov', 'video/webm'];

    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface;

    public function delete(Post $post): void;

    public function processMediaBatchUpload(Post $post): UploadedMediaInterface;

    public function upload(MediaPost $mediaPost, ?string $uploadUrl, SocialAccount $socialAccount, string $localPath): UploadedMediaIdInterface;
}
