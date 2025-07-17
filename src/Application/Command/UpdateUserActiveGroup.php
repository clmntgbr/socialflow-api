<?php

namespace App\Application\Command;

use App\Dto\User\PatchUser;
use App\Dto\User\PatchUserActiveGroup;
use App\Dto\User\PatchUserName;
use Symfony\Component\Uid\Uuid;

final class UpdateUserActiveGroup
{
    public function __construct(
        public Uuid $userId,
        public PatchUserActiveGroup $patchUserActiveGroup,
    ) {
    }
}
