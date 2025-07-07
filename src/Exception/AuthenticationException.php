<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;

class AuthenticationException extends UnauthorizedHttpException
{
    public function __construct(string $message = 'Authentication failed.', ?\Throwable $previous = null)
    {
        parent::__construct('Bearer', $message, $previous);
    }
}
