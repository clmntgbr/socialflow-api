<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\LinkedinAccessTokenParameters;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

class LinkedinSocialAccountService implements SocialAccountServiceInterface
{
    public function getConnectUrl(User $user): string
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

    /**
     * @param LinkedinAccessTokenParameters $params
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
