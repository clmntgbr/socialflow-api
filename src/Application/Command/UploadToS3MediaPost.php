<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class UploadToS3MediaPost
{
    public function __construct(
        public Uuid $mediaId,
    ) {
    }
}
