<?php

namespace App\Service\Publish;

use App\Application\Command\ExpireSocialAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\Publish\CreatePost\CreateFacebookPostPayload;
use App\Dto\Publish\GetPost\GetFacebookPost;
use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\FacebookPost;
use App\Entity\Post\Post;
use App\Exception\PublishException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class FacebookPublishService implements PublishServiceInterface
{
    private const FACEBOOK_API_VERSION = '/v23.0';
    private const FACEBOOK_API_URL = 'https://graph.facebook.com'.self::FACEBOOK_API_VERSION;
    private const FACEBOOK_POST = self::FACEBOOK_API_URL.'/%s/feed';

    public function __construct(
        private HttpClientInterface $httpClient,
        private MessageBusInterface $messageBus,
        private Denormalizer $denormalizer,
    ) {
    }

    /**
     * @param FacebookPost $post
     *
     * @return GetFacebookPost
     */
    public function post(Post $post): GetPostInterface
    {
        $socialAccount = $post->getCluster()->getSocialAccount();

        $payload = new CreateFacebookPostPayload(
            socialAccount: $socialAccount,
            post: $post,
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

            return $this->denormalizer->denormalize($response->toArray(), GetFacebookPost::class);
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

    /** @param FacebookPost $post */
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

            if (isset($content['success']) && $content['success'] === true) {
                return;
            }

            throw new PublishException('Failed to delete facebook post.');
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
