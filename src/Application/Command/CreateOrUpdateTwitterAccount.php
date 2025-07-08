<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\TwitterAccount;
use App\Dto\Token\AccessToken\TwitterAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateTwitterAccount implements CreateOrUpdateAccountInterface
{
    public function __construct(
        public Uuid $organizationId,
        public TwitterAccount $twitterAccount,
        public TwitterAccessToken $twitterToken,
    ) {
    }
}
