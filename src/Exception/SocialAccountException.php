<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class SocialAccountException extends HttpException
{
    public function __construct(string $message, int $code = Response::HTTP_BAD_REQUEST, ?\Throwable $previous = null)
    {
        parent::__construct(statusCode: $code, message: $message, previous: $previous);
    }
}
