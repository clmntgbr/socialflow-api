<?php

namespace App\Application\CommandHandler;

use App\Application\Command\DeleteDraftPost;
use App\Application\Command\RemoveMediaPost;
use App\Entity\Post\Post;
use App\Repository\Post\PostRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class DeleteDraftPostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(DeleteDraftPost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy([
            'id' => (string) $message->postId,
        ]);

        if (null === $post) {
            $this->logger->warning(sprintf('Failed to delete draft post: post with id [%s] was not found.', (string) $message->postId), ['id' => (string) $message->postId]);

            return;
        }

        if (!$post->isDraft()) {
            $this->logger->warning(sprintf('Cannot delete post with id [%s]: post is not a draft.', (string) $post->getId()), ['id' => (string) $post->getId()]);

            return;
        }

        foreach ($post->getMedias() as $media) {
            $this->messageBus->dispatch(new RemoveMediaPost(mediaPostId: $media->getId()), [
                new AmqpStamp('async-medium'),
            ]);
        }

        $this->postRepository->delete($post);
    }
}
