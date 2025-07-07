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
            $this->logger->warning(sprintf('Failed to delete post: post with id [%s] was not found.', (string) $message->postId), ['id' => (string) $message->postId]);

            return;
        }

        if (null === $post->getPostId()) {
            $this->logger->warning(sprintf('Failed to delete post: post with id [%s] does not have a remote postId.', (string) $post->getId()), ['id' => (string) $post->getId()]);

            return;
        }

        try {
            $publishService = $this->publish->get($post->getType());
            $publishService->delete($post);
        } catch (\Exception $exception) {
            throw new PublishException(message: 'Failed to delete post: '.$exception->getMessage());
        }
    }
}
