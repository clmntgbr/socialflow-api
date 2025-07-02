<?php

namespace App\Dto\Publish\GetPost;

final class GetTwitterPost implements GetPostInterface
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
