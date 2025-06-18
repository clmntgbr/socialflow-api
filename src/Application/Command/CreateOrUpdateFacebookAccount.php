<?php

namespace App\Application\Command;

use App\Dto\AccessToken\FacebookToken;
use App\Dto\SocialAccount\FacebookAccount;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateFacebookAccount
{
    public function __construct(
        public Uuid $accountId,
        public Uuid $userId,
        public Uuid $organizationId,
        public FacebookAccount $facebookAccount,
        public FacebookToken $facebookToken,
    ) {
    }
}
