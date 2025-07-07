<?php

namespace App\Dto\Publish\Upload;

use App\Entity\Post\MediaPost;
use App\Entity\SocialAccount\FacebookSocialAccount;

class UploadFacebookPayload implements UploadPayloadInterface
{
    public function __construct(
        private MediaPost $mediaPost, 
        private FacebookSocialAccount $socialAccount, 
        private string $localPath
    ) {  
    }

    public function getMediaPost(): MediaPost
    {
        return $this->mediaPost;
    }

    public function getSocialAccount(): FacebookSocialAccount
    {
        return $this->socialAccount;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }
}