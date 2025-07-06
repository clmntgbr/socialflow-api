<?php

namespace App\Dto\Publish\GetPost;

final class PublishedFacebookPost implements PublishedPostInterface
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
