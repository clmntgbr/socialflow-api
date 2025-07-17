<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\FacebookAccount;
use App\Dto\Token\AccessToken\FacebookAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateFacebookAccount implements CreateOrUpdateAccountInterface
{
    public function __construct(
        public Uuid $groupId,
        public FacebookAccount $facebookAccount,
        public FacebookAccessToken $facebookToken,
    ) {
    }
}
