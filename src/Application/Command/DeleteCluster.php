<?php

namespace App\Application\Command;

use App\Enum\SocialAccountStatus;
use Symfony\Component\Uid\Uuid;

final class DeleteCluster
{
    public function __construct(
        public Uuid $clusterId,
    ) {
    }
}
