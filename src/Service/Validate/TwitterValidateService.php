<?php

namespace App\Service\Validate;

use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Entity\Post\TwitterPost;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Exception\ContentValidationException;
use App\Service\Publish\PublishServiceInterface;

class TwitterValidateService implements ValidateServiceInterface
{
    /**
     * @param TwitterPost $post
     */
    public function validateContent(Post $post): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        $restrictions = $socialAccount->getRestrictions();
        $contentLength = mb_strlen($post->getContent() ?? '');

        if ($contentLength > $restrictions->getTextMaxCharacters()) {
            throw new ContentValidationException(message: sprintf('Content is too long: %s characters (max: %s)', $contentLength, $restrictions->getTextMaxCharacters()), postOrder: (string) $post->getOrder());
        }
    }

    public function validateMediaPost(MediaPost $mediaPost): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $mediaPost->getPost()->getCluster()->getSocialAccount();
        $restrictions = $socialAccount->getRestrictions();

        $maxFileSizesBytes = match (true) {
            in_array($mediaPost->getMimeType(), PublishServiceInterface::IMAGE_MIME_TYPES) => $restrictions->getImageMaxFileSizeBytes(),
            in_array($mediaPost->getMimeType(), PublishServiceInterface::VIDEO_MIME_TYPES) => $restrictions->getVideoMaxFileSizeBytes(),
            default => throw new ContentValidationException(message: 'Failed to validate media: Undefined mimetype', postOrder: (string) $mediaPost->getPost()->getOrder()),
        };

        if ($mediaPost->getSize() > $maxFileSizesBytes) {
            throw new ContentValidationException(message: sprintf('Media is too long: %s bytes (max: %s)', $mediaPost->getSize(), $maxFileSizesBytes), postOrder: (string) $mediaPost->getPost()->getOrder());
        }

        $maxFileDuration = match (true) {
            in_array($mediaPost->getMimeType(), PublishServiceInterface::IMAGE_MIME_TYPES) => null,
            in_array($mediaPost->getMimeType(), PublishServiceInterface::VIDEO_MIME_TYPES) => $restrictions->getVideoMaxDurationSeconds(),
            default => throw new ContentValidationException(message: 'Failed to validate media: Undefined mimetype', postOrder: (string) $mediaPost->getPost()->getOrder()),
        };

        if ($mediaPost->getDuration() > $maxFileDuration) {
            throw new ContentValidationException(message: sprintf('Media duration is too long: %s secondes (max: %s)', $mediaPost->getDuration(), $maxFileDuration), postOrder: (string) $mediaPost->getPost()->getOrder());
        }
    }
}
