<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\PublishedPostInterface;
use App\Entity\Post\Post;

interface PublishServiceInterface
{
    public function post(Post $post): PublishedPostInterface;

    public function delete(Post $post): void;

    public function uploadMedia();
}
