<?php

namespace App\Enum;

enum PostStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PROGRAMMED = 'programmed';
    case ERROR = 'error';

    public function getValue(): string
    {
        return $this->value;
    }
}
