<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class UpdateClusterStatus
{
    public function __construct(
        public Uuid $clusterId,
    ) {
    }
}
