<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;

class GetSocialAccountConnectUrl
{
    #[SerializedName('callback')]
    public ?string $frontCallback = '/';
}
