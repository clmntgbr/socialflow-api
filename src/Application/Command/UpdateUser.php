<?php

namespace App\Application\Command;

use App\Dto\User\PatchUser;
use Symfony\Component\Uid\Uuid;

final class UpdateUser
{
    public function __construct(
        public Uuid $userId,
        public PatchUser $patchUser,
    ) {
    }
}
