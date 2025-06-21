<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\LinkedinAccount;
use App\Dto\SocialAccount\TwitterAccount;
use App\Dto\Token\AccessToken\LinkedinAccessToken;
use App\Dto\Token\AccessToken\TwitterAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateLinkedinAccount
{
    public function __construct(
        public Uuid $accountId,
        public Uuid $userId,
        public Uuid $organizationId,
        public LinkedinAccount $linkedinAccount,
        public LinkedinAccessToken $linkedinToken,
    ) {
    }
}
