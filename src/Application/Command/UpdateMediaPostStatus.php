<?php

namespace App\Application\Command;

use App\Enum\MediaStatus;
use Symfony\Component\Uid\Uuid;

final class UpdateMediaPostStatus
{
    public function __construct(
        public Uuid $mediaPostId,
        public MediaStatus $status,
    ) {
    }
}
