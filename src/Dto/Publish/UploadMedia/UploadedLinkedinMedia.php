<?php

namespace App\Dto\Publish\UploadMedia;

class UploadedLinkedinMedia implements UploadedMediaInterface
{
    private array $medias = [];

    public function __construct()
    {
    }

    public function addMedia(InitializeUploadLinkedinMedia $initializeLinkedinUploadMedia)
    {
        $this->medias[] = $initializeLinkedinUploadMedia;
    }

    /**
     * @return InitializeUploadLinkedinMedia[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }
}
