<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class UploadFacebookMediaPost
{
    public function __construct(
        public Uuid $mediaId,
    ) {
    }
}
