<?php

namespace App\Dto\Publish\CreatePost;

use App\Entity\Post\FacebookPost;
use App\Entity\SocialAccount\SocialAccount;

final class CreateFacebookPostPayload implements \JsonSerializable
{
    public function __construct(
        private SocialAccount $socialAccount,
        private FacebookPost $post,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'message' => $this->post->getContent(),
            'link' => $this->post->getUrl(),
        ];
    }
}
