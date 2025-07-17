<?php

namespace App\Dto\User;

use Symfony\Component\Validator\Constraints as Assert;

class PatchUser
{
    public ?string $firstname = null;

    public ?string $lastname = null;

    public ?string $activeGroupId = null;
}
