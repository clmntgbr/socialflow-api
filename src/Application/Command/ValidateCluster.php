<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class ValidateCluster
{
    public function __construct(
        public Uuid $clusterId,
    ) {
    }
}
