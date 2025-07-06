<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CleanPost;
use App\Application\Command\DeleteDraftPost;
use App\Application\Command\RemoveMediaPost;
use App\Entity\Post\Post;
use App\Repository\Post\PostRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class CleanPostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(CleanPost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy([
            'id' => (string) $message->postId,
        ]);

        if (null === $post) {
            $this->logger->warning('Post does not exist.', ['id' => $message->postId]);

            return;
        }

        foreach ($post->getMedias() as $media) {
            $this->messageBus->dispatch(new RemoveMediaPost(mediaPostId: $media->getId(), delete: false), [
                new AmqpStamp('async-medium'),
            ]);
        }
    }
}
