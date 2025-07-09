<?php

namespace App\Service\Publish;

use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\UploadFacebookMediaPost;
use App\Denormalizer\Denormalizer;
use App\Dto\Publish\CreatePost\CreateFacebookPostPayload;
use App\Dto\Publish\PublishedPost\PublishedFacebookPost;
use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\Upload\uploadPayload;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\UploadMedia\UploadedFacebookMedia;
use App\Dto\Publish\UploadMedia\UploadedFacebookMediaId;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\FacebookPost;
use App\Entity\Post\Post;
use App\Entity\SocialAccount\FacebookSocialAccount;
use App\Exception\AuthenticationException;
use App\Exception\MethodNotImplementedException;
use App\Exception\PublishException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookPublishService implements PublishServiceInterface
{
    private const FACEBOOK_API_VERSION = '/v23.0';
    private const FACEBOOK_API_URL = 'https://graph.facebook.com'.self::FACEBOOK_API_VERSION;
    private const FACEBOOK_POST = self::FACEBOOK_API_URL.'/%s/feed';
    private const FACEBOOK_UPLOAD_MEDIA = self::FACEBOOK_API_URL.'/%s/photos';

    public function __construct(
        private HttpClientInterface $httpClient,
        private MessageBusInterface $messageBus,
        private Denormalizer $denormalizer,
    ) {
    }

    /**
     * @param FacebookPost          $post
     * @param UploadedFacebookMedia $medias
     *
     * @return PublishedFacebookPost
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        $socialAccount = $post->getCluster()->getSocialAccount();

        $payload = new CreateFacebookPostPayload(
            post: $post,
            medias: $medias,
        );

        $url = sprintf(self::FACEBOOK_POST, $socialAccount->getSocialAccountId());

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$post->getCluster()->getSocialAccount()->getToken(),
                    'Connection' => 'Keep-Alive',
                    'ContentType' => 'application/json',
                ],
                'body' => $payload->jsonSerialize(),
            ]);

            return $this->denormalizer->denormalize($response->toArray(), PublishedFacebookPost::class);
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: $exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param FacebookPost $post
     */
    public function delete(Post $post): void
    {
        $url = self::FACEBOOK_API_URL.'/'.$post->getPostId();

        try {
            $response = $this->httpClient->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$post->getCluster()->getSocialAccount()->getToken(),
                    'Connection' => 'Keep-Alive',
                    'ContentType' => 'application/json',
                ],
            ]);

            $content = $response->toArray();

            if (!isset($content['success']) || !$content['success']) {
                throw new PublishException('Failed to delete Facebook post: the API did not confirm deletion.');
            }
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $post->getCluster()->getSocialAccount()->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: $exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param FacebookPost $post
     *
     * @return UploadedFacebookMedia
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        $uploadedMedia = new UploadedFacebookMedia();

        foreach ($post->getMedias() as $media) {
            try {
                /** @var ?UploadedFacebookMediaId $mediaId */
                $mediaId = $this->messageBus->dispatch(new UploadFacebookMediaPost(
                    mediaId: $media->getId(),
                ))->last(HandledStamp::class)?->getResult();

                if (null === $mediaId) {
                    throw new PublishException(message: 'Failed to upload Facebook media: the upload handler did not return a media ID.', code: Response::HTTP_BAD_REQUEST);
                }

                $uploadedMedia->addMedia($mediaId);
            } catch (\Exception $exception) {
                throw new PublishException(message: 'Failed to process Facebook media batch upload: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
            }
        }

        return $uploadedMedia;
    }

    /**
     * @param uploadPayload $uploadPayload
     *
     * @return UploadedFacebookMediaId
     */
    public function upload(UploadPayloadInterface $uploadPayload): UploadedMediaIdInterface
    {
        return match (true) {
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::IMAGE_MIME_TYPES) => $this->uploadMedia($uploadPayload->getSocialAccount(), $uploadPayload->getLocalPath()),
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::VIDEO_MIME_TYPES) => $this->uploadVideo($uploadPayload->getSocialAccount(), $uploadPayload->getLocalPath()),
            default => throw new PublishException('Failed to upload media to Facebook: Undefined mimetype'),
        };
    }

    private function uploadMedia(
        FacebookSocialAccount $socialAccount,
        string $localPath,
    ): UploadedFacebookMediaId {
        $url = sprintf(self::FACEBOOK_UPLOAD_MEDIA, $socialAccount->getSocialAccountId());

        try {
            $response = $this->httpClient->request('POST', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$socialAccount->getToken(),
                    'Connection' => 'Keep-Alive',
                    'ContentType' => 'application/json',
                ],
                'body' => [
                    'source' => fopen($localPath, 'r'),
                    'published' => false,
                ],
            ]);

            if (in_array($response->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new AuthenticationException('Authentication failed: invalid or expired Facebook token.', null);
            }

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new PublishException('Failed to upload media to Facebook: API returned status code '.$response->getStatusCode(), $response->getStatusCode());
            }

            return $this->denormalizer->denormalize($response->toArray(), UploadedFacebookMediaId::class);
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to upload media to Facebook: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    private function uploadVideo(
        FacebookSocialAccount $socialAccount,
        string $localPath,
    ): UploadedFacebookMediaId {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
