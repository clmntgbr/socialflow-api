<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;

class YoutubeAccountPublicMetrics
{
    #[SerializedName('subscriberCount')]
    public int $followers = 0;

    #[SerializedName('videoCount')]
    public int $videos = 0;

    #[SerializedName('viewCount')]
    public int $views = 0;

    public function setFollowers(string $followers): void
    {
        $this->followers = (int) $followers;
    }

    public function setVideos(string $videos): void
    {
        $this->videos = (int) $videos;
    }

    public function setViews(string $views): void
    {
        $this->views = (int) $views;
    }
}
