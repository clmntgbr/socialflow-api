<?php

namespace App\Dto\SocialAccount\GetAccounts;

use App\Dto\SocialAccount\LinkedinAccount;
use App\Dto\SocialAccount\TwitterAccount;

class LinkedinGetAccounts extends AbstractGetAccounts
{
    public function __construct(
        public readonly LinkedinAccount $linkedinAccount,
    ) {
    }
}
