<?php

namespace App\Enum;

enum PostStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case PROGRAMMED = 'programmed';

    public function getValue(): string
    {
        return $this->value;
    }
}
