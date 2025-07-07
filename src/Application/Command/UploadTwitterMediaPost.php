<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class UploadTwitterMediaPost
{
    public function __construct(
        public Uuid $mediaId,
    ) {
    }
}
