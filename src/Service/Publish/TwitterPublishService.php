<?php

namespace App\Service\Publish;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\UploadTwitterMediaPost;
use App\Dto\Publish\CreatePost\CreateTwitterPostPayload;
use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\PublishedPost\PublishedTwitterPost;
use App\Dto\Publish\Upload\UploadPayloadInterface;
use App\Dto\Publish\Upload\UploadTwitterPayload;
use App\Dto\Publish\UploadMedia\UploadedMediaIdInterface;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Dto\Publish\UploadMedia\UploadedTwitterMedia;
use App\Dto\Publish\UploadMedia\UploadedTwitterMediaId;
use App\Entity\Post\Post;
use App\Entity\Post\TwitterPost;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Exception\PublishException;
use App\Repository\Post\PostRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\SerializerInterface;

class TwitterPublishService implements PublishServiceInterface
{
    private const MAX_ATTEMPS = 50;

    public function __construct(
        private PostRepository $postRepository,
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
        private LoggerInterface $logger,
        private readonly string $twitterApiKey,
        private readonly string $twitterApiSecret,
    ) {
    }

    /**
     * @param TwitterPost $post
     */
    public function post(Post $post, UploadedMediaInterface $medias): PublishedPostInterface
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        /** @var TwitterPost $previousPost */
        $previousPost = $this->postRepository->getPreviousPost($post);

        $payload = new CreateTwitterPostPayload(
            post: $post,
            previousPost: $previousPost,
            medias: $medias,
        );

        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion('2');

            $response = $twitterOAuth->post('tweets', $payload->jsonSerialize(), ['jsonPayload' => true]);

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED])) {
                $error = $response->detail ?? 'Failed to authenticate with Twitter API: received status code '.$response->status;
                throw new PublishException($error, $response->status);
            }

            if (!isset($response->data) && !isset($response->data->id)) {
                $error = $response->detail ?? 'Unknown error';
                throw new PublishException($error, Response::HTTP_BAD_REQUEST);
            }

            return $this->serializer->deserialize(json_encode($response->data), PublishedTwitterPost::class, 'json');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: $exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param TwitterPost $post
     */
    public function delete(Post $post): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion('2');

            $response = $twitterOAuth->delete('tweets/'.$post->getPostId());

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED])) {
                throw new PublishException('Failed to authenticate with Twitter API: received status code '.$response->status, $response->status);
            }

            if (!isset($response->data->deleted) || !$response->data->deleted) {
                throw new PublishException('Failed to delete Twitter post: the API did not confirm deletion.');
            }
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $post->getCluster()->getSocialAccount()->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to delete Twitter post: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /**
     * @param TwitterPost $post
     */
    public function processMediaBatchUpload(Post $post): UploadedMediaInterface
    {
        $uploadedMedia = new UploadedTwitterMedia();

        foreach ($post->getMedias() as $media) {
            try {
                /** @var ?UploadedTwitterMediaId $mediaId */
                $mediaId = $this->messageBus->dispatch(new UploadTwitterMediaPost(
                    mediaId: $media->getId(),
                ))->last(HandledStamp::class)?->getResult();

                if (null === $mediaId) {
                    throw new PublishException(message: 'Failed to upload Twitter media: the upload handler did not return a media ID.', code: Response::HTTP_BAD_REQUEST);
                }

                $uploadedMedia->addMedia($mediaId);
            } catch (\Exception $exception) {
                throw new PublishException(message: 'Failed to process Twitter media', code: Response::HTTP_BAD_REQUEST, previous: $exception);
            }
        }

        return $uploadedMedia;
    }

    /**
     * @param UploadTwitterPayload $uploadPayload
     *
     * @return UploadedTwitterMediaId
     */
    public function upload(UploadPayloadInterface $uploadPayload): UploadedMediaIdInterface
    {
        return match (true) {
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::IMAGE_MIME_TYPES) => $this->uploadMedia($uploadPayload->getSocialAccount(), $uploadPayload->getLocalPath()),
            in_array($uploadPayload->getMediaPost()->getMimeType(), self::VIDEO_MIME_TYPES) => $this->uploadVideo($uploadPayload->getSocialAccount(), $uploadPayload->getLocalPath()),
            default => throw new PublishException('Failed to upload media to Twitter: Undefined mimetype'),
        };
    }

    private function uploadMedia(
        TwitterSocialAccount $socialAccount,
        string $localPath,
    ): UploadedTwitterMediaId {
        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion('1.1');

            $response = $twitterOAuth->upload('media/upload', ['media' => $localPath], ['chunkedUpload' => true]);

            if (!isset($response->media_id_string) && !isset($response->media_id_string)) {
                throw new PublishException('Failed to upload media to Twitter');
            }

            return new UploadedTwitterMediaId($response->media_id_string, 'image');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to upload media to Twitter: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    private function uploadVideo(
        TwitterSocialAccount $socialAccount,
        string $localPath,
    ): UploadedTwitterMediaId {
        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion('1.1');

            $response = $twitterOAuth->upload('media/upload', [
                'media' => $localPath,
                'media_type' => 'video/mp4',
                'media_category' => 'tweet_video',
            ], ['chunkedUpload' => true]);

            if (!isset($response->media_id_string) && !isset($response->media_id_string)) {
                throw new PublishException('Failed to upload media to Twitter');
            }

            return new UploadedTwitterMediaId($response->media_id_string, 'video');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to upload media to Twitter: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    public function checkUploadStatus(TwitterSocialAccount $socialAccount, UploadedTwitterMediaId $mediaId): void
    {
        $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
        $twitterOAuth->setApiVersion('1.1');

        $attempts = 0;

        while ($attempts < self::MAX_ATTEMPS) {
            $response = $twitterOAuth->mediaStatus($mediaId->mediaId);

            $this->logger->info(json_encode($response));

            if (!isset($response->processing_info)) {
                return;
            }

            $processingInfo = $response->processing_info;

            match ($processingInfo->state) {
                'succeeded' => (function () { return; })(),
                'failed' => throw new PublishException('Media processing failed: '.($processingInfo->error->message ?? 'Unknown error')),
                'in_progress' => sleep($processingInfo->check_after_secs ?? 10),
                default => sleep(10),
            };

            if ('succeeded' === $processingInfo->state) {
                return;
            }

            ++$attempts;
        }
    }
}
