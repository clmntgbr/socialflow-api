<?php

namespace App\Dto\Token\AccessToken;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class LinkedinAccessToken extends AbstractAccessToken
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('access_token')]
    public ?string $accessToken;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('token_type')]
    public ?string $tokenType;

    #[Assert\NotBlank()]
    #[Assert\Type('int')]
    #[SerializedName('expires_in')]
    public ?int $expiresIn;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('scope')]
    public ?string $scope;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('id_token')]
    public ?string $idToken;
}
