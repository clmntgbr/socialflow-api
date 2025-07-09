<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\Post;

interface PublishServiceInterface
{
    public const IMAGE_MIME_TYPES = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    public const GIF_MIME_TYPES = ['image/gif', 'image/x-gif', 'application/gif', 'application/x-gif'];
    public const VIDEO_MIME_TYPES = ['video/mp4', 'video/avi', 'video/mov', 'video/webm'];

    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface;

    public function delete(Post $post): void;

    public function processMediaBatchUpload(Post $post): UploadedMediaInterface;

    public function upload(UploadPayloadInterface $uploadPayloadInterface): UploadedMediaIdInterface;
}
