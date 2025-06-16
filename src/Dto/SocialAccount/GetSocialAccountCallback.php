<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Validator\Constraints as Assert;

class GetSocialAccountCallback
{
    #[Assert\Type(type: 'string')]
    #[Assert\NotBlank(groups: ['linkedin', 'facebook'], message: 'Code value should not be blank')]
    public ?string $code = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank(groups: ['twitter'], message: 'OauthToken value should not be blank')]
    public ?string $oauthToken = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank(groups: ['twitter'], message: 'OauthVerifier value should not be blank')]
    public ?string $oauthVerifier = null;

    #[Assert\Type('string')]
    #[Assert\NotBlank(message: 'State value should not be blank')]
    public ?string $state = null;
}
