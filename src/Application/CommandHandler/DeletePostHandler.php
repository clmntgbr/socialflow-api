<?php

namespace App\Application\CommandHandler;

use App\Application\Command\DeletePost;
use App\Entity\Post\Post;
use App\Exception\PublishException;
use App\Repository\Post\PostRepository;
use App\Service\Publish\PublishServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeletePostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private LoggerInterface $logger,
        private PublishServiceFactory $publish,
    ) {
    }

    public function __invoke(DeletePost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy([
            'id' => (string) $message->postId,
        ]);

        if (null === $post) {
            $this->logger->warning('Post does not exist.', ['id' => $message->postId]);

            return;
        }

        if (null === $post->getPostId()) {
            $this->logger->warning('Post does not have postId.', ['id' => $post->getId()]);

            return;
        }

        try {
            $publishService = $this->publish->get($post->getType());
            $publishService->delete($post);
        } catch (\Exception $exception) {
            throw new PublishException(message: $exception->getMessage());
        }
    }
}
