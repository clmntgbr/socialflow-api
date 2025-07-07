<?php

namespace App\Dto\Publish\UploadMedia;

class UploadedLinkedinMedia implements UploadedMediaInterface
{
    private array $medias = [];

    public function __construct()
    {
    }

    public function addMedia(UploadedLinkedinMediaId $uploadedLinkedinMediaId)
    {
        $this->medias[] = $uploadedLinkedinMediaId;
    }

    /**
     * @return UploadedLinkedinMediaId[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }
}
