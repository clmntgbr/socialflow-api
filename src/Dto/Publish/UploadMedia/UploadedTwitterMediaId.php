<?php

namespace App\Dto\Publish\UploadMedia;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class UploadedTwitterMediaId implements UploadedMediaIdInterface
{
    public function __construct(
        #[Assert\Type('string')]
        #[Assert\NotBlank()]
        #[SerializedName('media_id_string')]
        public string $mediaId
    ) {   
    }
}
