<?php

namespace App\Dto\Publish\CreatePost;

use App\Entity\Post\TwitterPost;
use App\Entity\SocialAccount\SocialAccount;

final class CreateTwitterPostPayload implements \JsonSerializable
{
    public function __construct(
        private TwitterPost $post,
        private ?TwitterPost $previousPost,
    ) {
    }

    public function jsonSerialize(): array
    {
        $data = [
            'text' => $this->post->getContent(),
        ];

        if (null !== $this->previousPost) {
            $data['reply'] = [
                'in_reply_to_tweet_id' => $this->previousPost->getPostId(),
            ];
        }

        return $data;
    }
}
