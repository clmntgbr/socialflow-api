<?php

namespace App\Dto\Publish\PublishedPost;

final class PublishedTwitterPost implements PublishedPostInterface
{
    public function __construct(
        public string $id,
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
