<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Dto\Token\AccessTokenParameters\InstagramAccessTokenParameters;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;

class InstagramSocialAccountService implements SocialAccountServiceInterface
{
    public const FACEBOOK_API_URL = 'https://graph.facebook.com';
    public const FACEBOOK_LOGIN_URL = 'https://www.facebook.com/v21.0';

    public function __construct(
        private UserRepository $userRepository,
        private string $facebookClientId,
        private string $facebookClientSecret,
    ) {
    }

    public function getConnectUrl(User $user): string
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getScopes(): array
    {
        return [];
    }

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    /**
     * @param InstagramAccessTokenParameters $params
     */
    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getLongAccessToken(string $token): AbstractAccessToken
    {
        throw new MethodNotImplementedException(__METHOD__);
    }

    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts
    {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
