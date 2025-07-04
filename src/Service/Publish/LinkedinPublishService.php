<?php

namespace App\Service\Publish;

use App\Application\Command\ExpireSocialAccount;
use App\Denormalizer\Denormalizer;
use App\Dto\Publish\CreatePost\CreateLinkedinPostPayload;
use App\Dto\Publish\GetPost\GetLinkedinPost;
use App\Dto\Publish\GetPost\GetPostInterface;
use App\Entity\Post\LinkedinPost;
use App\Entity\Post\Post;
use App\Exception\PublishException;
use App\Repository\Post\PostRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class LinkedinPublishService implements PublishServiceInterface
{
    private const LINKEDIN_API_URL = 'https://api.linkedin.com';
    private const LINKEDIN_POST = self::LINKEDIN_API_URL.'/rest/posts';

    public function __construct(
        private PostRepository $postRepository,
        private HttpClientInterface $httpClient,
        private MessageBusInterface $messageBus,
        private Denormalizer $denormalizer,
        private string $twitterClientId,
        private string $twitterClientSecret,
        private readonly string $twitterApiKey,
        private readonly string $twitterApiSecret,
    ) {
    }

    /**
     * @param LinkedinPost $post
     */
    public function post(Post $post): GetPostInterface
    {
        $socialAccount = $post->getCluster()->getSocialAccount();

        $payload = new CreateLinkedinPostPayload(
            socialAccount: $socialAccount,
            post: $post,
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
                throw new \Exception('Authentication error occurred', $response->status);
            }

            if (Response::HTTP_CREATED !== $response->getStatusCode()) {
                throw new \Exception('Publication error occurred', $response->getStatusCode());
            }

            return $this->denormalizer->denormalize($response->getHeaders(), GetLinkedinPost::class);
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

    /** @param LinkedinPost $post */
    public function delete(Post $post): void
    {
        throw new \RuntimeException('Method not implemented.');
    }

    public function uploadMedia()
    {
        throw new \RuntimeException('Method not implemented.');
    }
}
