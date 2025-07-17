<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\LinkedinAccount;
use App\Dto\Token\AccessToken\LinkedinAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateLinkedinAccount implements CreateOrUpdateAccountInterface
{
    public function __construct(
        public Uuid $groupId,
        public LinkedinAccount $linkedinAccount,
        public LinkedinAccessToken $linkedinToken,
    ) {
    }
}
