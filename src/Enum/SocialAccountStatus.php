<?php

namespace App\Enum;

enum SocialAccountStatus: string
{
    case PENDING_ACTIVATION = 'pending_activation';
    case ACTIVE = 'active';
    case EXPIRED = 'expired';

    public function getValue(): string
    {
        return $this->value;
    }
}
