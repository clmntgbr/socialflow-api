<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GroupNotFoundException extends NotFoundHttpException
{
    public function __construct(string $groupId, ?\Throwable $previous = null)
    {
        $message = sprintf('Group with id [%s] was not found.', $groupId);
        parent::__construct($message, $previous);
    }
}
