<?php

namespace App\Dto\Publish\UploadMedia;

class UploadedLinkedinMedia implements UploadedMediaInterface
{
    private array $medias = [];

    public function __construct()
    {
    }

    public function addMedia(InitializeLinkedinUploadMedia $initializeLinkedinUploadMedia)
    {
        $this->medias[] = $initializeLinkedinUploadMedia;
    }

    /**
     * @return InitializeLinkedinUploadMedia[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }
}
