<?php

namespace App\Dto\SocialAccount;

use Symfony\Component\Serializer\Attribute\SerializedName;

class TwitterAccountPublicMetrics
{
    #[SerializedName('followers_count')]
    public int $followers = 0;

    #[SerializedName('following_count')]
    public int $followings = 0;

    #[SerializedName('like_count')]
    public int $likes = 0;

    #[SerializedName('tweet_count')]
    public int $tweets = 0;
}
