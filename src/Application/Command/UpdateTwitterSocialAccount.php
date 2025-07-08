<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\TwitterAccount;
use App\Entity\SocialAccount\TwitterSocialAccount;
use Symfony\Component\Uid\Uuid;

final class UpdateTwitterSocialAccount
{
    public function __construct(
        public string $socialAccountId,
        public TwitterAccount $twitterAccount,
    ) {
    }
}
