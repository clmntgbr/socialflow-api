<?php

namespace App\Dto\Publish\CreatePost;

use App\Entity\SocialAccount\SocialAccount;

final class CreateLinkedinPostPayload implements \JsonSerializable
{
    public function __construct(
        private SocialAccount $socialAccount,
        private string $content,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'author' => sprintf('urn:li:organization:%s', $this->socialAccount->getSocialAccountId()),
            'commentary' => $this->content,
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
}
