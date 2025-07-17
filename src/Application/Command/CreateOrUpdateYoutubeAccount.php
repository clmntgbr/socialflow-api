<?php

namespace App\Application\Command;

use App\Dto\SocialAccount\YoutubeAccount;
use App\Dto\Token\AccessToken\YoutubeAccessToken;
use Symfony\Component\Uid\Uuid;

final class CreateOrUpdateYoutubeAccount implements CreateOrUpdateAccountInterface
{
    public function __construct(
        public Uuid $groupId,
        public YoutubeAccount $youtubeAccount,
        public YoutubeAccessToken $youtubeToken,
    ) {
    }
}
