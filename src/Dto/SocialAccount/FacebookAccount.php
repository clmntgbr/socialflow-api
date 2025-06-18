<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Serializer\Attribute\SerializedPath;
use Symfony\Component\Validator\Constraints as Assert;

class FacebookAccount extends AbstractAccount
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('id')]
    public string $id;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('name')]
    public string $username;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('access_token')]
    public string $token;

    #[Assert\Type('string')]
    #[SerializedPath('[emails][0]')]
    public string $email;

    #[Assert\Type('string')]
    #[SerializedPath('[picture][data][url]')]
    public string $picture;

    #[Assert\Type('int')]
    #[SerializedName('followers_count')]
    public int $follower;

    #[Assert\Type('int')]
    #[SerializedName('fan_count')]
    public int $following;

    #[Assert\Type('string')]
    #[SerializedName('link')]
    public ?string $link = null;

    #[Assert\Type('string')]
    #[SerializedName('website')]
    public ?string $website = null;
}
