<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class UploadMediaException extends BadRequestHttpException
{
    public function __construct(string $message = 'An error occurred during media upload.', ?\Throwable $previous = null)
    {
        parent::__construct($message, $previous);
    }
}
