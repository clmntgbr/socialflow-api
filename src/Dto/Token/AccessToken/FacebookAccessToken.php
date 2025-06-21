<?php

namespace App\Dto\Token\AccessToken;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class FacebookAccessToken extends AbstractAccessToken
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('access_token')]
    public ?string $token;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('token_type')]
    public ?string $tokenType;
}
