<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class GetSocialAccountCallback
{
    #[Assert\Type(type: 'string')]
    public ?string $code = null;

    #[Assert\Type('string')]
    #[SerializedName('oauth_token')]
    public ?string $oauthToken = null;

    #[Assert\Type('string')]
    #[SerializedName('oauth_verifier')]
    public ?string $oauthVerifier = null;

    #[Assert\Type('string')]
    public ?string $state = null;
}
