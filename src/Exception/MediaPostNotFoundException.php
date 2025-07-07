<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaPostNotFoundException extends NotFoundHttpException
{
    public function __construct(string $mediaPostId, ?\Throwable $previous = null)
    {
        $message = sprintf('MediaPost with id [%s] was not found.', $mediaPostId);
        parent::__construct($message, $previous);
    }
}
