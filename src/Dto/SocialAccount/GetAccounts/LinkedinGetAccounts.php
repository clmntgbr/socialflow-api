<?php

namespace App\Dto\SocialAccount\GetAccounts;

use App\Dto\SocialAccount\LinkedinAccount;

class LinkedinGetAccounts extends AbstractGetAccounts
{
    public function __construct(
        public readonly LinkedinAccount $linkedinAccount,
    ) {
    }
}
