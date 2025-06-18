<?php

namespace App\Service\SocialAccount;

use App\Dto\AccessToken\AbstractToken;
use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;
use Symfony\Component\HttpFoundation\RedirectResponse;

interface SocialAccountServiceInterface
{
    public const FACEBOOK_CALLBACK_URL = '/social-account/facebook/callback';
    public const TWITTER_CALLBACK_URL = '/social-account/twitter/callback';
    public const INSTAGRAM_CALLBACK_URL = '/social-account/instagram/callback';
    public const YOUTUBE_CALLBACK_URL = '/social-account/youtube/callback';
    public const THREAD_CALLBACK_URL = '/social-account/thread/callback';
    public const LINKEDIN_CALLBACK_URL = '/social-account/linkedin/callback';

    public function getConnectUrl(User $user, string $callback): string;

    public function getScopes(): array;

    public function create(GetSocialAccountCallback $getSocialAccountCallback): RedirectResponse;

    public function delete();

    public function getAccessToken(string $code): ?AbstractToken;

    public function getLongAccessToken(string $token): ?AbstractToken;

    public function getAccounts(AbstractToken $token): array;
}
