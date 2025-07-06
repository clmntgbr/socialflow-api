<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class CleanPost
{
    public function __construct(
        public Uuid $postId,
    ) {
    }
}
