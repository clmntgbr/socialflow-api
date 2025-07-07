<?php

namespace App\Dto\Publish\UploadMedia;

use App\Entity\SocialAccount\LinkedinSocialAccount;

final class UploadedLinkedinMediaIdPayload implements \JsonSerializable
{
    public function __construct(
        public LinkedinSocialAccount $linkedinSocialAccount,
    ) {
    }

    public function jsonSerialize(): array
    {
        return [
            'initializeUploadRequest' => [
                'owner' => 'urn:li:person:'.$this->linkedinSocialAccount->getSocialAccountId(),
            ],
        ];
    }

    public function encode(): string
    {
        return json_encode($this->jsonSerialize());
    }
}
