<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\HttpException;

class MethodNotImplementedException extends HttpException
{
    public function __construct(string $methodName, ?\Throwable $previous = null)
    {
        $message = sprintf('Method [%s] is not implemented.', $methodName);
        parent::__construct(501, $message, $previous);
    }
}
