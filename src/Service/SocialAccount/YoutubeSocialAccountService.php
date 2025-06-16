<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;
use App\Repository\UserRepository;

class YoutubeSocialAccountService implements SocialAccountServiceInterface
{
    public const FACEBOOK_API_URL = 'https://graph.facebook.com';
    public const FACEBOOK_LOGIN_URL = 'https://www.facebook.com/v21.0';

    public function __construct(
        private UserRepository $userRepository,
        private string $facebookClientId,
        private string $facebookClientSecret,
    ) {
    }

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
