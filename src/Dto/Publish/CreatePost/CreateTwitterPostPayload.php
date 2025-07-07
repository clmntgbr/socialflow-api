<?php

namespace App\Dto\Publish\CreatePost;

use App\Dto\Publish\UploadMedia\UploadedTwitterMedia;
use App\Entity\Post\TwitterPost;

final class CreateTwitterPostPayload implements \JsonSerializable
{
    public function __construct(
        private TwitterPost $post,
        private ?TwitterPost $previousPost,
        private UploadedTwitterMedia $medias,
    ) {
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'text' => $this->post->getContent(),
        ];

        if (null !== $this->previousPost) {
            $payload['reply'] = [
                'in_reply_to_tweet_id' => $this->previousPost->getPostId(),
            ];
        }

        $medias = array_map(
            fn ($media) => $media->mediaId,
            $this->medias->getMedias()
        );

        if (!empty($medias)) {
            $payload['media'] = ['media_ids' => $medias];
        }

        return $payload;
    }
}
