<?php

namespace App\Dto\SocialAccount\GetAccounts;

use App\Dto\SocialAccount\TwitterAccount;

class TwitterGetAccounts extends AbstractGetAccounts
{
    public function __construct(
        public readonly TwitterAccount $twitterAccount,
    ) {
    }
}
