<?php

namespace App\Dto\Publish\PublishedPost;

use Symfony\Component\Serializer\Attribute\SerializedPath;

final class PublishedLinkedinPost implements PublishedPostInterface
{
    private string $id;

    public function __construct(
        #[SerializedPath('[x-restli-id][0]')]
        private string $urn,
    ) {
        $this->id = str_replace('urn:li:share:', '', $urn);
    }

    public function getId(): string
    {
        return $this->id;
    }
}
