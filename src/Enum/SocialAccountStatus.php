<?php

namespace App\Enum;

enum SocialAccountStatus: string
{
    case PENDING_VALIDATION = 'pending_validation';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';

    public function getValue(): string
    {
        return $this->value;
    }
}
