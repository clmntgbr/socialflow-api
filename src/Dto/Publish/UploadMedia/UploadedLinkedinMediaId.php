<?php

namespace App\Dto\Publish\UploadMedia;

use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Validator\Constraints as Assert;

class UploadedLinkedinMediaId implements UploadedMediaIdInterface
{
    #[Assert\Type('int')]
    #[Assert\NotBlank()]
    #[SerializedPath('[value][uploadUrlExpiresAt]')]
    public int $uploadUrlExpiresAt;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedPath('[value][uploadUrl]')]
    public string $uploadUrl;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedPath('[value][image]')]
    public string $mediaId;
}
