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
    private const FACEBOOK_API_URL = 'https://graph.facebook.com';
    private const FACEBOOK_POST = self::FACEBOOK_API_URL.'/v23.0/%s/feed';

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
                    'Authorization' => sprintf('Bearer %s', $post->getCluster()->getSocialAccount()->getToken()),
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

    public function delete()
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
