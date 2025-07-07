<?php

namespace App\Dto\Publish\UploadMedia;

class UploadedFacebookMedia implements UploadedMediaInterface
{
    private array $medias = [];

    public function __construct()
    {
    }

    public function addMedia(UploadedFacebookMediaId $uploadedFacebookMediaId)
    {
        $this->medias[] = $uploadedFacebookMediaId;
    }

    /**
     * @return UploadedFacebookMediaId[]
     */
    public function getMedias(): array
    {
        return $this->medias;
    }
}
