<?php

namespace App\Dto\Token\AccessTokenParameters;

use Symfony\Component\Validator\Constraints as Assert;

class YoutubeAccessTokenParameters extends AbstractAccessTokenParameters
{
    #[Assert\Type(type: 'string')]
    public ?string $code = null;

    public function __construct(?string $code = null)
    {
        $this->code = $code;
    }
}
