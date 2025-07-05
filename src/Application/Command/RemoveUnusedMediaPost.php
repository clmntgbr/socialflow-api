<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class RemoveUnusedMediaPost
{
    public function __construct(
        public Uuid $mediaPostId,
    ) {
    }
}
