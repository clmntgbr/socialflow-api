<?php

namespace App\Dto\Publish\UploadMedia;

class UploadedTwitterMedia implements UploadedMediaInterface
{
    private array $medias = [];

    public function __construct()
    {
    }

    public function addMedia(UploadedTwitterMediaId $uploadedTwitterMediaId)
    {
        $this->medias[] = $uploadedTwitterMediaId;
    }

    /**
     * @return UploadedTwitterMediaId[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }
}
