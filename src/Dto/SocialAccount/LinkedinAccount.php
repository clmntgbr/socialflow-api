<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class LinkedinAccount extends AbstractAccount
{
    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    #[SerializedName('sub')]
    public string $id;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $name;

    #[Assert\Type('array')]
    public array $locale;

    #[Assert\Type('string')]
    #[Assert\NotBlank()]
    public string $email;

    #[SerializedName('given_name')]
    public ?string $givenName;

    #[SerializedName('family_name')]
    public ?string $familyName;

    public ?string $picture;

    #[SerializedName('email_verified')]
    public bool $verified = false;
}
