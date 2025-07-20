<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class SocialAccountOnDelete
{
    public function __construct(
        public Uuid $socialAccountId,
    ) {
    }
}
