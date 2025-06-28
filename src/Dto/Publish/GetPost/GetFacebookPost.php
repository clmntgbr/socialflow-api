<?php

namespace App\Dto\Publish\GetPost;

final class GetFacebookPost implements GetPostInterface
{
    public function __construct(
        public string $id
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }
}
