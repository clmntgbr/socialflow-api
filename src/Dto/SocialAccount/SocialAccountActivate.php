<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;
use Symfony\Component\Validator\Constraints as Assert;

class SocialAccountActivate
{
    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('id')]
    public string $id;

    #[Assert\NotBlank()]
    #[Assert\Type('string')]
    #[SerializedName('status')]
    public string $status;
} 