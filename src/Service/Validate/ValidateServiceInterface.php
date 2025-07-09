<?php

namespace App\Service\Validate;

use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;

interface ValidateServiceInterface
{
    public function validateContent(Post $post): void;

    public function validateMediaPost(MediaPost $mediaPost): void;
}
