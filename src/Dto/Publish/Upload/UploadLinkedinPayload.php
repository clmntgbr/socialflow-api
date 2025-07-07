<?php

namespace App\Dto\Publish\Upload;

use App\Dto\Publish\UploadMedia\UploadedLinkedinMediaId;
use App\Entity\Post\MediaPost;
use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Entity\SocialAccount\TwitterSocialAccount;

class UploadLinkedinPayload implements UploadPayloadInterface
{
    public function __construct(
        private MediaPost $mediaPost, 
        private LinkedinSocialAccount $socialAccount, 
        private UploadedLinkedinMediaId $uploadedLinkedinMediaId,
        private string $localPath
    ) {  
    }

    public function getMediaPost(): MediaPost
    {
        return $this->mediaPost;
    }

    public function getSocialAccount(): LinkedinSocialAccount
    {
        return $this->socialAccount;
    }

    public function getLocalPath(): string
    {
        return $this->localPath;
    }

    public function getUploadedLinkedinMediaId(): UploadedLinkedinMediaId
    {
        return $this->uploadedLinkedinMediaId;
    }
}