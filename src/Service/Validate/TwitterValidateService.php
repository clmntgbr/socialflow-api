<?php

namespace App\Service\Validate;

use App\Dto\SocialAccount\Restrictions\TwitterRestrictionNotVerified;
use App\Dto\SocialAccount\Restrictions\TwitterRestrictionVerified;
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

    /**
     * @param TwitterPost $post
     */
    public function validateMaxFiles(Post $post): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();
        $restrictions = $socialAccount->getRestrictions();

        $videoQuantity = $post->getVideoQuantity();
        $gifQuantity = $post->getGifQuantity();
        $imageQuantity = $post->getImageQuantity();

        if ($videoQuantity > $restrictions->getVideoMaxFile()) {
            throw new ContentValidationException(message: 'Maximum 1 video allowed per post', postOrder: (string) $post->getOrder());
        }
        
        if ($gifQuantity > $restrictions->getGifMaxFile()) {
            throw new ContentValidationException(message: 'Maximum 1 GIF allowed per post', postOrder: (string) $post->getOrder());
        }
        
        if ($imageQuantity > $restrictions->getImageMaxFile()) {
            throw new ContentValidationException(message: 'Maximum 4 images allowed per post', postOrder: (string) $post->getOrder());
        }

        $totalMediaTypes = ($videoQuantity > 0 ? 1 : 0) + ($gifQuantity > 0 ? 1 : 0) + ($imageQuantity > 0 ? 1 : 0);
        if ($totalMediaTypes > 1) {
            throw new ContentValidationException(message: 'Cannot mix videos, GIFs and images in the same post', postOrder: (string) $post->getOrder());
        }
    }

    public function validateMediaPost(MediaPost $mediaPost): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $mediaPost->getPost()->getCluster()->getSocialAccount();
        $restrictions = $socialAccount->getRestrictions();

        $maxFileSizesBytes = match (true) {
            in_array($mediaPost->getMimeType(), PublishServiceInterface::IMAGE_MIME_TYPES) => $restrictions->getImageMaxFileSizeBytes(),
            in_array($mediaPost->getMimeType(), PublishServiceInterface::GIF_MIME_TYPES) => $restrictions->getGifMaxFileSizeBytes(),
            in_array($mediaPost->getMimeType(), PublishServiceInterface::VIDEO_MIME_TYPES) => $restrictions->getVideoMaxFileSizeBytes(),
            default => throw new ContentValidationException(message: 'Failed to validate media: Undefined mimetype', postOrder: (string) $mediaPost->getPost()->getOrder(), mediaPostOrder: (string) $mediaPost->getOrder()),
        };

        if ($mediaPost->getSize() > $maxFileSizesBytes) {
            throw new ContentValidationException(message: sprintf('Media is too long: %s bytes (max: %s)', $mediaPost->getSize(), $maxFileSizesBytes), postOrder: (string) $mediaPost->getPost()->getOrder(), mediaPostOrder: (string) $mediaPost->getOrder());
        }

        $maxFileDuration = match (true) {
            in_array($mediaPost->getMimeType(), PublishServiceInterface::IMAGE_MIME_TYPES) => null,
            in_array($mediaPost->getMimeType(), PublishServiceInterface::GIF_MIME_TYPES) => null,
            in_array($mediaPost->getMimeType(), PublishServiceInterface::VIDEO_MIME_TYPES) => $restrictions->getVideoMaxDurationSeconds(),
            default => throw new ContentValidationException(message: 'Failed to validate media: Undefined mimetype', postOrder: (string) $mediaPost->getPost()->getOrder(), mediaPostOrder: (string) $mediaPost->getOrder()),
        };

        if ($mediaPost->getDuration() > $maxFileDuration) {
            throw new ContentValidationException(message: sprintf('Media duration is too long: %s secondes (max: %s)', $mediaPost->getDuration(), $maxFileDuration), postOrder: (string) $mediaPost->getPost()->getOrder(), mediaPostOrder: (string) $mediaPost->getOrder());
        }
    }
}
