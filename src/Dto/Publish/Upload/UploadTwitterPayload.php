<?php

namespace App\Dto\Publish\Upload;

use App\Entity\Post\MediaPost;
use App\Entity\SocialAccount\TwitterSocialAccount;

class UploadTwitterPayload implements UploadPayloadInterface
{
    public function __construct(
        private MediaPost $mediaPost,
        private TwitterSocialAccount $socialAccount,
        private string $localPath,
    ) {
    }

    public function getMediaPost(): MediaPost
    {
        return $this->mediaPost;
    }

    public function getSocialAccount(): TwitterSocialAccount
    {
        return $this->socialAccount;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }
}
