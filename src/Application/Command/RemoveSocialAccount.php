<?php

namespace App\Application\Command;

use App\Enum\SocialAccountStatus;
use Symfony\Component\Uid\Uuid;

final class RemoveSocialAccount
{
    public function __construct(
        public Uuid $socialAccountId,
        public SocialAccountStatus $status,
    ) {
    }
}
