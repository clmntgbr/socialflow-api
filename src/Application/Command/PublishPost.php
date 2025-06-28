<?php

namespace App\Application\Command;

use App\Entity\SocialAccount\SocialAccount;
use Symfony\Component\Uid\Uuid;

final class PublishPost
{
    public function __construct(
        public Uuid $postId,
    ) {
    }
}
