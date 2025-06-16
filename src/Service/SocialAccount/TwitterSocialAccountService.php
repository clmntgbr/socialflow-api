<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;

class TwitterSocialAccountService implements SocialAccountServiceInterface
{
    public function getConnectUrl(User $user, string $callback): string
    {
        return '';
    }

    public function getScopes(): array
    {
        return [];
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
