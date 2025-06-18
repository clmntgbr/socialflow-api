<?php

namespace App\Application\Command;

use Symfony\Component\Uid\Uuid;

final class CreateOrganization
{
    public function __construct(
        public Uuid $userId,
    ) {
    }
}
