<?php

namespace App\Dto\Token\AccessToken;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class YoutubeAccessToken extends AbstractAccessToken
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('access_token')]
    public ?string $token;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('token_type')]
    public ?string $tokenType;

    #[Assert\NotBlank()]
    #[Assert\Type('int')]
    #[SerializedName('expires_in')]
    public ?int $expiresIn;

    #[Assert\Type('string')]
    #[SerializedName('refresh_token')]
    public ?string $refreshToken = null;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('id_token')]
    public ?string $idToken;
}
