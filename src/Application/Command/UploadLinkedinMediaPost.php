<?php

namespace App\Application\Command;

use App\Dto\Publish\UploadMedia\InitializeLinkedinUploadMedia;
use Symfony\Component\Uid\Uuid;

final class UploadLinkedinMediaPost
{
    public function __construct(
        public Uuid $mediaId,
        public InitializeLinkedinUploadMedia $initializeLinkedinUploadMedia,
    ) {
    }
}
