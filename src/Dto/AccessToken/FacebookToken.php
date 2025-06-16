<?php

namespace App\Dto\AccessToken;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class FacebookToken extends AbstractToken
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
