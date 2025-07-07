<?php

namespace App\Service\Publish;

use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\UploadLinkedinMediaPost;
use App\Denormalizer\Denormalizer;
use App\Dto\Publish\CreatePost\CreateLinkedinPostPayload;
use App\Dto\Publish\PublishedPost\PublishedLinkedinPost;
use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\Upload\UploadLinkedinPayload;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\UploadMedia\UploadedLinkedinMediaId;
use App\Dto\Publish\UploadMedia\UploadedLinkedinMediaIdPayload;
use App\Dto\Publish\UploadMedia\UploadedLinkedinMedia;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Entity\Post\LinkedinPost;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Entity\SocialAccount\LinkedinSocialAccount;
use App\Entity\SocialAccount\SocialAccount;
use App\Exception\AuthenticationException;
use App\Exception\MethodNotImplementedException;
use App\Exception\PublishException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LinkedinPublishService implements PublishServiceInterface
{
    private const LINKEDIN_API_URL = 'https://api.linkedin.com';
    private const LINKEDIN_POST = self::LINKEDIN_API_URL.'/rest/posts';
    private const LINKEDIN_INITIALIZE_UPLOAD_MEDIA = self::LINKEDIN_API_URL.'/rest/images?action=initializeUpload';

    public function __construct(
        private HttpClientInterface $httpClient,
        private MessageBusInterface $messageBus,
        private Denormalizer $denormalizer,
    ) {
    }

    /**
     * @param LinkedinPost          $post
     * @param UploadedLinkedinMedia $medias
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        $socialAccount = $post->getCluster()->getSocialAccount();

        $payload = new CreateLinkedinPostPayload(
            socialAccount: $socialAccount,
            post: $post,
            medias: $medias,
        );

        try {
            $response = $this->httpClient->request('POST', self::LINKEDIN_POST, [
                'headers' => [
                    'Authorization' => 'Bearer '.$post->getCluster()->getSocialAccount()->getToken(),
                    'Connection' => 'Keep-Alive',
                    'Content-Type: application/json',
                    'LinkedIn-Version: 202411',
                    'X-Restli-Protocol-Version: 2.0.0',
                ],
                'body' => $payload->encode(),
            ]);

            if (in_array($response->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new AuthenticationException('Authentication failed: invalid or expired Linkedin token.', null);
            }

            if (Response::HTTP_CREATED !== $response->getStatusCode()) {
                throw new PublishException('Failed to publish post to Linkedin: API returned status code '.$response->getStatusCode(), $response->getStatusCode());
            }

            return $this->denormalizer->denormalize($response->getHeaders(), PublishedLinkedinPost::class);
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to publish post to Linkedin: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param LinkedinPost $post
     */
    public function delete(Post $post): void
    {
        $socialAccount = $post->getCluster()->getSocialAccount();

        $url = self::LINKEDIN_POST.'/'.urlencode('urn:li:share:'.$post->getPostId());

        try {
            $response = $this->httpClient->request('DELETE', $url, [
                'headers' => [
                    'Authorization' => 'Bearer '.$post->getCluster()->getSocialAccount()->getToken(),
                    'Connection' => 'Keep-Alive',
                    'Content-Type: application/json',
                    'LinkedIn-Version: 202411',
                    'X-Restli-Protocol-Version: 2.0.0',
                ],
            ]);

            if (in_array($response->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new AuthenticationException('Authentication failed: invalid or expired Linkedin token.', null);
            }

            if (Response::HTTP_NO_CONTENT !== $response->getStatusCode()) {
                throw new PublishException('Failed to delete Linkedin post: API returned status code '.$response->getStatusCode(), $response->getStatusCode());
            }
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to delete Linkedin post: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param LinkedinPost $post
     *
     * @return UploadedLinkedinMedia
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        $uploadedMedia = new UploadedLinkedinMedia();
        /** @var LinkedinSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        foreach ($post->getMedias() as $media) {
            $initializeUploadMedia = $this->initializeUploadMedia($socialAccount);
            try {
                $this->messageBus->dispatch(new UploadLinkedinMediaPost(
                    mediaId: $media->getId(),
                    uploadedLinkedinMediaId: $initializeUploadMedia,
                ));
            } catch (\Exception $exception) {
                throw new PublishException(message: 'Failed to process Linkedin media batch upload: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
            }

            $uploadedMedia->addMedia($initializeUploadMedia);
        }

        return $uploadedMedia;
    }

    private function initializeUploadMedia(LinkedinSocialAccount $socialAccount): UploadedLinkedinMediaId
    {
        $payload = new UploadedLinkedinMediaIdPayload(
            linkedinSocialAccount: $socialAccount,
        );

        try {
            $response = $this->httpClient->request('POST', self::LINKEDIN_INITIALIZE_UPLOAD_MEDIA, [
                'headers' => [
                    'Authorization' => 'Bearer '.$socialAccount->getToken(),
                    'Connection' => 'Keep-Alive',
                    'Content-Type: application/json',
                    'LinkedIn-Version: 202411',
                    'X-Restli-Protocol-Version: 2.0.0',
                ],
                'body' => $payload->encode(),
            ]);

            if (in_array($response->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new AuthenticationException('Authentication failed: invalid or expired Linkedin token.', null);
            }

            if (Response::HTTP_OK !== $response->getStatusCode()) {
                throw new PublishException('Failed to initialize Linkedin media upload: API returned status code '.$response->getStatusCode(), $response->getStatusCode());
            }

            return $this->denormalizer->denormalize($response->toArray(), UploadedLinkedinMediaId::class);
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to initialize Linkedin media upload: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param UploadLinkedinPayload $uploadPayload
     */
    public function upload(UploadPayloadInterface $uploadPayload): UploadedMediaIdInterface
    {
        return match (true) {
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::IMAGE_MIME_TYPES) => $this->uploadMedia($uploadPayload->getSocialAccount(), $uploadPayload->getUploadedLinkedinMediaId(), $uploadPayload->getLocalPath()),
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::VIDEO_MIME_TYPES) => $this->uploadVideo($uploadPayload->getSocialAccount(), $uploadPayload->getUploadedLinkedinMediaId(), $uploadPayload->getLocalPath()),
            default => throw new PublishException('Failed to upload media to Linkedin: Undefined mimetype'),
        };
    }

    private function uploadMedia(
        LinkedinSocialAccount $socialAccount,
        UploadedLinkedinMediaId $uploadedLinkedinMediaId,
        string $localPath,
    ): UploadedLinkedinMediaId {
        try {
            $response = $this->httpClient->request('PUT', $uploadedLinkedinMediaId->uploadUrl, [
                'headers' => [
                    'authorization' => sprintf('Bearer %s', $socialAccount->getToken()),
                    'linkedin-version' => '202411',
                    'x-restli-protocol-version' => '2.0.0',
                    'content-type' => 'application/octet-stream',
                ],
                'body' => fopen($localPath, 'r'),
            ]);

            if (in_array($response->getStatusCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new AuthenticationException('Authentication failed: invalid or expired Linkedin token.', null);
            }

            if (Response::HTTP_CREATED !== $response->getStatusCode()) {
                throw new PublishException('Failed to upload media to Linkedin: API returned status code '.$response->getStatusCode(), $response->getStatusCode());
            }

            return $uploadedLinkedinMediaId;
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to upload media to Linkedin: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    private function uploadVideo(
        LinkedinSocialAccount $socialAccount,
        UploadedLinkedinMediaId $uploadedLinkedinMediaId,
        string $localPath,
    ): void {
        throw new MethodNotImplementedException(__METHOD__);
    }
}
