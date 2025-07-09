<?php

namespace App\Service\Validate;

use App\Exception\ProviderNotSupportedException;

class ValidateServiceFactory
{
    public function __construct(
        private FacebookValidateService $facebookService,
        private LinkedinValidateService $linkedinService,
        private TwitterValidateService $twitterService,
        private YoutubeValidateService $youtubeService,
        private ThreadValidateService $threadService,
        private InstagramValidateService $instagramService,
    ) {
    }

    public function get(string $provider): ValidateServiceInterface
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
