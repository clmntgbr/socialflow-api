<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;

class LinkedinSocialAccountService implements SocialAccountServiceInterface
{
    public function getConnectUrl(User $user, string $callback): string
    {
        return '';
    }

    public function getScopes(): array
    {
        return [
            'profile',
            'email,openid',
            'w_member_social',
        ];
    }

    public function create(GetSocialAccountCallback $getSocialAccountCallback)
    {
    }

    public function delete()
    {
    }

    public function getToken(string $code)
    {
    }

    public function getAccount()
    {
    }
}
