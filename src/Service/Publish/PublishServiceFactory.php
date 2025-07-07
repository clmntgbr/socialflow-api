<?php

namespace App\Service\Publish;

class PublishServiceFactory
{
    public function __construct(
        private FacebookPublishService $facebookService,
        private LinkedinPublishService $linkedinService,
        private TwitterPublishService $twitterService,
        private YoutubePublishService $youtubeService,
        private ThreadPublishService $threadService,
        private InstagramPublishService $instagramService,
    ) {
    }

    public function get(string $provider): PublishServiceInterface
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
