<?php

namespace App\Service\Validate;

use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;

class ThreadValidateService implements ValidateServiceInterface
{
    public function validateContent(Post $post): void
    {
    }

    public function validateMediaPost(MediaPost $mediaPost): void
    {
    }

    public function validateMaxFiles(Post $post): void
    {
    }
}
