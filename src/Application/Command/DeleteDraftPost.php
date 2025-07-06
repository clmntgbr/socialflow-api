<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class DeleteDraftPost
{
    public function __construct(
        public Uuid $postId,
    ) {
    }
}
