<?php

namespace App\Dto\SocialAccount\GetAccounts;

use App\Dto\SocialAccount\FacebookAccount;

class FacebookGetAccounts extends AbstractGetAccounts
{
    /**
     * @param FacebookAccount[] $facebookAccounts
     */
    public function __construct(
        public readonly array $facebookAccounts,
    ) {
    }
}
