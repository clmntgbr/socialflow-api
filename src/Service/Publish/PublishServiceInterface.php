<?php

namespace App\Service\Publish;

use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\Post;

interface PublishServiceInterface
{
    public function post(Post $post): GetPostInterface;

    public function delete(Post $post): void;

    public function uploadMedia();
}
