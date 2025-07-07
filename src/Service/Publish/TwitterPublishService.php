<?php

namespace App\Service\Publish;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Application\Command\ExpireSocialAccount;
use App\Application\Command\UploadTwitterMediaPost;
use App\Dto\Publish\CreatePost\CreateTwitterPostPayload;
use App\Dto\Publish\PublishedPost\PublishedPostInterface;
use App\Dto\Publish\PublishedPost\PublishedTwitterPost;
use App\Dto\Publish\UploadMedia\UploadedMediaInterface;
use App\Dto\Publish\UploadMedia\UploadedTwitterMedia;
use App\Dto\Publish\UploadMedia\UploadedTwitterMediaId;
use App\Entity\Post\Post;
use App\Entity\Post\TwitterPost;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Exception\PublishException;
use App\Repository\Post\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Serializer\SerializerInterface;

class TwitterPublishService implements PublishServiceInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
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

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new PublishException('Failed to authenticate with Twitter API: received status code '.$response->status, $response->status);
            }

            if (!isset($response->data) && !isset($response->data->id)) {
                $error = $response->title ?? 'Unknown error';
                throw new PublishException("Failed to publish tweet: $error", Response::HTTP_BAD_REQUEST);
            }

            return $this->serializer->deserialize(json_encode($response->data), PublishedTwitterPost::class, 'json');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to publish tweet: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
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

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new PublishException('Failed to authenticate with Twitter API: received status code '.$response->status, $response->status);
            }

            if (!isset($response->data->deleted) || !$response->data->deleted) {
                throw new PublishException('Failed to delete Twitter post: the API did not confirm deletion.');
            }
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
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
                throw new PublishException(message: 'Failed to process Twitter media batch upload: '.$exception->getMessage(), code: Response::HTTP_BAD_REQUEST, previous: $exception);
            }
        }

        return $uploadedMedia;
    }

    public function uploadMedia(
        TwitterSocialAccount $socialAccount,
        string $localPath,
    ): UploadedTwitterMediaId {
        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion('1.1');

            $response = $twitterOAuth->upload('media/upload', ['media' => $localPath], ['chunkedUpload' => true]);

            if (!isset($response->media_id_string) && !isset($response->media_id_string)) {
                throw new PublishException('Failed to upload media to Twutter');
            }

            return $this->serializer->deserialize(json_encode($response), UploadedTwitterMediaId::class, 'json');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(id: $socialAccount->getId()), [
                    new AmqpStamp('async-high'),
                ]);
            }

            throw new PublishException(message: 'Failed to upload media to Twitter: '.$exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }
}
