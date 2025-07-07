<?php

namespace App\Dto\Publish\CreatePost;

use App\Dto\Publish\UploadMedia\UploadedLinkedinMedia;
use App\Entity\Post\LinkedinPost;
use App\Entity\SocialAccount\SocialAccount;

final class CreateLinkedinPostPayload implements \JsonSerializable
{
    public function __construct(
        private SocialAccount $socialAccount,
        private LinkedinPost $post,
        private UploadedLinkedinMedia $medias,
    ) {
    }

    public function jsonSerialize(): array
    {
        $payload = [
            'author' => 'urn:li:person:'.$this->socialAccount->getSocialAccountId(),
            'commentary' => $this->post->getContent(),
            'visibility' => 'PUBLIC',
            'distribution' => [
                'feedDistribution' => 'MAIN_FEED',
                'targetEntities' => [],
                'thirdPartyDistributionChannels' => [],
            ],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];

        $medias = array_map(
            fn ($media) => ['id' => $media->mediaId],
            $this->medias->getMedias()
        );

        if (!empty($medias)) {
            $payload['content'] = $this->buildContentBasedOnMediaCount($medias);
        }

        return $payload;
    }

    private function buildContentBasedOnMediaCount(array $medias): array
    {
        $mediaCount = count($medias);

        return match (true) {
            1 === $mediaCount => $this->buildSingleMediaContent($medias[0]),
            $mediaCount >= 2 => $this->buildMultiImageContent($medias),
            default => [],
        };
    }

    private function buildMultiImageContent(array $medias): array
    {
        $limitedMedias = array_slice($medias, 0, 20);

        return [
            'multiImage' => [
                'images' => $limitedMedias,
            ],
        ];
    }

    private function buildSingleMediaContent(array $media): array
    {
        return [
            'media' => [
                'id' => $media['id'],
                'title' => $media['altText'],
            ],
        ];
    }

    public function encode(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
