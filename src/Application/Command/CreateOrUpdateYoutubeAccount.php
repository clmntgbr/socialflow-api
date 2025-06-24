<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\YoutubeAccount;
use App\Dto\Token\AccessToken\YoutubeAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateYoutubeAccount
{
    public function __construct(
        public Uuid $accountId,
        public Uuid $userId,
        public Uuid $organizationId,
        public YoutubeAccount $youtubeAccount,
        public YoutubeAccessToken $youtubeToken,
    ) {
    }
}
