<?php

namespace App\Enum;

enum MediaStatus: string
{
    case CREATED = 'created';
    case PROCESSING = 'processing';
    case UPLOADED = 'uploaded';

    public function getValue(): string
    {
        return $this->value;
    }
}
