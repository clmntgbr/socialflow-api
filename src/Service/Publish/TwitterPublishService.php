<?php

namespace App\Service\Publish;

use Abraham\TwitterOAuth\TwitterOAuth;
use App\Application\Command\ExpireSocialAccount;
use App\Dto\Publish\CreatePost\CreateTwitterPostPayload;
use App\Dto\Publish\GetPost\GetPostInterface;
use App\Dto\Publish\GetPost\GetTwitterPost;
use App\Entity\Post\Post;
use App\Entity\Post\TwitterPost;
use App\Entity\SocialAccount\TwitterSocialAccount;
use App\Exception\PublishException;
use App\Repository\Post\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\SerializerInterface;

class TwitterPublishService implements PublishServiceInterface
{
    public function __construct(
        private PostRepository $postRepository,
        private MessageBusInterface $messageBus,
        private SerializerInterface $serializer,
        private string $twitterClientId,
        private string $twitterClientSecret,
        private readonly string $twitterApiKey,
        private readonly string $twitterApiSecret,
    ) {
    }

    /**
     * @param TwitterPost $post
     */
    public function post(Post $post): GetPostInterface
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        $previousPost = $this->postRepository->getPreviousPost($post);

        $payload = new CreateTwitterPostPayload(
            socialAccount: $socialAccount,
            post: $post,
            previousPost: $previousPost,
        );

        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion(2);

            $response = $twitterOAuth->post('tweets', $payload->jsonSerialize(), ['jsonPayload' => true]);

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new PublishException('Authentication error occurred', $response->status);
            }

            if (!isset($response->data) && !isset($response->data->id)) {
                $error = $response->title ?? 'An error occurred';
                throw new PublishException("Request failed: $error", Response::HTTP_BAD_REQUEST);
            }

            return $this->serializer->deserialize(json_encode($response->data), GetTwitterPost::class, 'json');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(
                    id: $socialAccount->getId()), [
                        new AmqpStamp('async'),
                    ]
                );
            }

            throw new PublishException(message: $exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    /** @param TwitterPost $post */
    public function delete(Post $post): void
    {
        /** @var TwitterSocialAccount $socialAccount */
        $socialAccount = $post->getCluster()->getSocialAccount();

        try {
            $twitterOAuth = new TwitterOAuth($this->twitterApiKey, $this->twitterApiSecret, $socialAccount->getToken(), $socialAccount->getTokenSecret());
            $twitterOAuth->setApiVersion(2);

            $response = $twitterOAuth->delete('tweets/' . $post->getPostId());

            if (isset($response->status) && in_array($response->status, [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                throw new PublishException('Authentication error occurred', $response->status);
            }

            if (isset($response->data) && isset($response->data->deleted) && true === $response->data->deleted) {
                return;
            }

            throw new PublishException('Failed to delete twitter post.');
        } catch (\Exception $exception) {
            if (in_array($exception->getCode(), [Response::HTTP_UNAUTHORIZED, Response::HTTP_FORBIDDEN])) {
                $this->messageBus->dispatch(new ExpireSocialAccount(
                    id: $post->getCluster()->getSocialAccount()->getId()), [
                        new AmqpStamp('async'),
                    ]
                );
            }

            throw new PublishException(message: $exception->getMessage(), code: Response::HTTP_NOT_FOUND, previous: $exception);
        }
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
