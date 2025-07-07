<?php

namespace App\Exception;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ClusterNotFoundException extends NotFoundHttpException
{
    public function __construct(string $clusterId, ?\Throwable $previous = null)
    {
        $message = sprintf('Cluster with id [%s] was not found.', $clusterId);
        parent::__construct($message, $previous);
    }
}
