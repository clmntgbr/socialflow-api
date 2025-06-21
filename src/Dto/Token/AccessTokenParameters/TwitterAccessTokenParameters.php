<?php

namespace App\Dto\Token\AccessTokenParameters;

use Symfony\Component\Validator\Constraints as Assert;

class TwitterAccessTokenParameters extends AbstractAccessTokenParameters
{
    #[Assert\Type('string')]
    #[Assert\NotBlank]
    public ?string $oauthToken = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank]
    public ?string $oauthVerifier = null;

    public function __construct(
        ?string $oauthToken = null,
        ?string $oauthVerifier = null,
    ) {
        $this->oauthToken = $oauthToken;
        $this->oauthVerifier = $oauthVerifier;
    }
}
