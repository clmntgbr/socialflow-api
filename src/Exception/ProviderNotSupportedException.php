<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ProviderNotSupportedException extends BadRequestHttpException
{
    public function __construct(string $provider, ?\Throwable $previous = null)
    {
        $message = sprintf('Provider [%s] is not supported for this operation.', $provider);
        parent::__construct($message, $previous);
    }
}
