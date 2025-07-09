<?php

namespace App\Service\Validate;

use App\Entity\Post\FacebookPost;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Exception\ContentValidationException;

class FacebookValidateService extends ValidateServiceAbstract implements ValidateServiceInterface
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
