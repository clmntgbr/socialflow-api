<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Validator\Constraints as Assert;

class YoutubeAccount extends AbstractAccount
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $id;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedPath('[snippet][title]')]
    public string $name;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedPath('[snippet][description]')]
    public ?string $description = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedPath('[snippet][customUrl]')]
    public string $username;

    #[Assert\Type('string')]
    #[SerializedPath('[snippet][thumbnails][high][url]')]
    public ?string $picture = null;

    public bool $verified = false;

    #[Assert\Valid()]
    #[SerializedName('statistics')]
    public YoutubeAccountPublicMetrics $publicMetrics;

    public function setDescription(?string $description): void
    {
        if (empty($description)) {
            return;
        }

        $this->description = $description;
    }
}
