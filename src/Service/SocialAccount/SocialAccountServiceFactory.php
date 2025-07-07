<?php

namespace App\Service\SocialAccount;

class SocialAccountServiceFactory
{
    public function __construct(
        private FacebookSocialAccountService $facebookService,
        private LinkedinSocialAccountService $linkedinService,
        private TwitterSocialAccountService $twitterService,
        private YoutubeSocialAccountService $youtubeService,
        private ThreadSocialAccountService $threadService,
        private InstagramSocialAccountService $instagramService,
    ) {
    }

    public function get(string $provider): SocialAccountServiceInterface
    {
        return match ($provider) {
            'facebook' => $this->facebookService,
            'linkedin' => $this->linkedinService,
            'twitter' => $this->twitterService,
            'instagram' => $this->instagramService,
            'thread' => $this->threadService,
            'youtube' => $this->youtubeService,
            default => throw new ProviderNotSupportedException($provider),
        };
    }
}
