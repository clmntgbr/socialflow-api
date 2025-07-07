<?php

namespace App\Dto\Publish\UploadMedia;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class UploadedFacebookMediaId
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedName('id')]
    public string $mediaId;
}
