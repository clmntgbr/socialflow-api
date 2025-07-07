<?php

namespace App\Dto\Publish\CreatePost;

use App\Dto\Publish\UploadMedia\UploadedFacebookMedia;
use App\Entity\Post\FacebookPost;
use App\Entity\SocialAccount\SocialAccount;

final class CreateFacebookPostPayload implements \JsonSerializable
{
    public function __construct(
        private FacebookPost $post,
        private UploadedFacebookMedia $medias,
    ) {
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'message' => $this->post->getContent(),
            'link' => $this->post->getUrl(),
        ];

        $medias = array_map(
            fn ($media) => ['media_fbid' => $media->mediaId],
            $this->medias->getMedias()
        );

        if (!empty($medias)) {
            $payload['attached_media'] = $medias;
        }

        return $payload;
    }
}
