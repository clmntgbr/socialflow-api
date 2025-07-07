<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class UserNotFoundException extends NotFoundHttpException
{
    public function __construct(string $userId, ?\Throwable $previous = null)
    {
        $message = sprintf('User with id [%s] was not found.', $userId);
        parent::__construct($message, $previous);
    }
}
