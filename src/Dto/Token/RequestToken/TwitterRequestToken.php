<?php

namespace App\Dto\Token\RequestToken;

use App\Dto\Token\AccessToken\AbstractAccessToken;
use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class TwitterRequestToken extends AbstractAccessToken
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('oauth_token')]
    public ?string $oauthToken;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('oauth_token_secret')]
    public ?string $oauthTokenSecret;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('oauth_callback_confirmed')]
    public ?string $oauthCallbackConfirmed;
}
