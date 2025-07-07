<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class OrganizationNotFoundException extends NotFoundHttpException
{
    public function __construct(string $organizationId, ?\Throwable $previous = null)
    {
        $message = sprintf('Organization with id [%s] was not found.', $organizationId);
        parent::__construct($message, $previous);
    }
}
