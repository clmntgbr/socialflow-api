<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class TwitterAccount extends AbstractAccount
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $username;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $name;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $id;

    #[Assert\Valid()]
    #[SerializedName('public_metrics')]
    public TwitterAccountPublicMetrics $publicMetrics;

    public bool $verified;

    #[SerializedName('profile_image_url')]
    public ?string $profileImageUrl;
}
