<?php

namespace App\Service\Publish;

use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\Post;

interface PublishServiceInterface
{
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface;

    public function delete(Post $post): void;

    public function uploadMedias(Post $post): UploadedMediaInterface;
}
