<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class GetSocialAccountCallback
{
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank(groups: ['linkedin', 'facebook', 'youtube'])]
    public ?string $code = null;

    #[Assert\Type('string')]
    #[SerializedName('oauth_token')]
    #[Assert\NotBlank(groups: ['twitter'])]
    public ?string $oauthToken = null;

    #[Assert\Type('string')]
    #[SerializedName('oauth_verifier')]
    #[Assert\NotBlank(groups: ['twitter'])]
    public ?string $oauthVerifier = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public ?string $state = null;
}
