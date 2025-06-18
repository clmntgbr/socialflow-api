<?php

namespace App\Enum;

enum SocialAccountStatus: string
{
    case ACTIVE = 'active';
    case TO_VALIDATE = 'to_validate';
    case DESACTIVATED = 'desactivated';

    public function getValue(): string
    {
        return $this->value;
    }
}
