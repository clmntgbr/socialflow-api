<?php

namespace App\Dto\SocialAccount\GetAccounts;

use App\Dto\SocialAccount\YoutubeAccount;

class YoutubeGetAccounts extends AbstractGetAccounts
{
    /**
     * @param YoutubeAccount[] $youtubeAccounts
     */
    public function __construct(
        public readonly array $youtubeAccounts,
    ) {
    }
}
