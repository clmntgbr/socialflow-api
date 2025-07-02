<?php

namespace App\Dto\Publish\CreatePost;

use App\Entity\Post\LinkedinPost;
use App\Entity\SocialAccount\SocialAccount;

final class CreateLinkedinPostPayload implements \JsonSerializable
{
    public function __construct(
        private SocialAccount $socialAccount,
        private LinkedinPost $post,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'author' => sprintf('urn:li:person:%s', $this->socialAccount->getSocialAccountId()),
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
    }

    public function encode(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
