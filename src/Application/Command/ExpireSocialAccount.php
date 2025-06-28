<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class ExpireSocialAccount
{
    public function __construct(
        public Uuid $id,
    ) {
    }
}
