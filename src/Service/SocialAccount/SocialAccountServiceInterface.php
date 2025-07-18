<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetAccounts\AbstractGetAccounts;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use App\Dto\Token\AccessTokenParameters\AbstractAccessTokenParameters;
use App\Entity\SocialAccount\SocialAccount;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface SocialAccountServiceInterface
{
    public const FACEBOOK_CALLBACK_URL = '/social_accounts/facebook/callback';
    public const TWITTER_CALLBACK_URL = '/social_accounts/twitter/callback';
    public const INSTAGRAM_CALLBACK_URL = '/social_accounts/instagram/callback';
    public const YOUTUBE_CALLBACK_URL = '/social_accounts/youtube/callback';
    public const THREAD_CALLBACK_URL = '/social_accounts/thread/callback';
    public const LINKEDIN_CALLBACK_URL = '/social_accounts/linkedin/callback';

    public function getConnectUrl(User $user): string;

    public function getScopes(): array;

    public function getMe(SocialAccount $socialAccount): void;

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse;

    public function getAccessToken(AbstractAccessTokenParameters $params): AbstractAccessToken;

    public function getLongAccessToken(string $token): AbstractAccessToken;

    public function getAccessTokenFromRefreshToken(string $token): AbstractAccessToken;

    public function getAccounts(AbstractAccessToken $token): AbstractGetAccounts;
}
