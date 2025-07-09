<?php

namespace App\Service\Validate;

use App\Entity\Post\Post;
use App\Exception\ContentValidationException;
use App\Exception\ProviderNotSupportedException;

abstract class ValidateServiceAbstract
{
    public function validateMediaPostStatus(Post $post): void
    {
        if ($post->hasPublishedMedias()) {
            throw new ContentValidationException(message: 'This post contain published medias. Please create a new media.', postOrder: (string) $post->getOrder());
        }
    }
}
