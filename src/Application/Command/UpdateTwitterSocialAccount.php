<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\TwitterAccount;

final class UpdateTwitterSocialAccount
{
    public function __construct(
        public string $socialAccountId,
        public TwitterAccount $twitterAccount,
    ) {
    }
}
