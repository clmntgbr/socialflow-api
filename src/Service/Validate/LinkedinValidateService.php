<?php

namespace App\Service\Validate;

use App\Entity\Post\LinkedinPost;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Exception\ContentValidationException;

class LinkedinValidateService extends ValidateServiceAbstract implements ValidateServiceInterface
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
