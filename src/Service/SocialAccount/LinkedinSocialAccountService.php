<?php

namespace App\Service\SocialAccount;

use App\Dto\AccessToken\AbstractToken;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

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

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse
    {
        return new RedirectResponse('');
    }

    public function delete()
    {
    }

    public function getAccessToken(string $code): ?AbstractToken
    {
        return null;
    }

    public function getLongAccessToken(string $token): ?AbstractToken
    {
        return null;
    }

    public function getAccounts(AbstractToken $token): array
    {
        return [];
    }
}
