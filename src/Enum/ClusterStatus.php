<?php

namespace App\Enum;

enum ClusterStatus: string
{
    case PUBLISHED = 'published';
    case DRAFT = 'draft';
    case ARCHIVED = 'archived';
    case ERROR = 'error';
    case PARTIAL_ERROR = 'partial_error';
    case PROGRAMMED = 'programmed';

    public function getValue(): string
    {
        return $this->value;
    }
}
