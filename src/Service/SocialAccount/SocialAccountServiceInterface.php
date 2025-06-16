<?php

namespace App\Service\SocialAccount;

use App\Dto\SocialAccount\GetSocialAccountCallback;
use App\Entity\User;

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

    public function create(GetSocialAccountCallback $getSocialAccountCallback);

    public function delete();

    public function getToken(string $code);

    public function getAccount();
}
