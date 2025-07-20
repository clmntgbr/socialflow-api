<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class SocialAccountOnActivation
{
    public function __construct(
        public Uuid $socialAccountId,
    ) {
    }
}
