<?php

namespace App\Application\CommandHandler;

use App\Application\Command\ValidateMediaPost;
use App\Application\Command\ValidatePost;
use App\Entity\Post\MediaPost;
use App\Entity\Post\Post;
use App\Repository\Post\PostRepository;
use App\Service\Validate\ValidateServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ValidatePostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private ValidateServiceFactory $validateServiceFactory,
    ) {
    }

    public function __invoke(ValidatePost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy(['id' => (string) $message->postId]);

        if (null === $post) {
            $this->logger->info(sprintf('Cannot publish post: post with id [%s] was not found.', (string) $message->postId), ['id' => (string) $message->postId]);

            return;
        }

        $service = $this->validateServiceFactory->get($post->getType());
        $service->validateContent($post);
        $service->validateMediaPostStatus($post);
        $service->validateMaxFiles($post);

        array_map(
            fn (MediaPost $media) => $this->messageBus->dispatch(
                new ValidateMediaPost(mediaId: $media->getId())
            ),
            $post->getMedias()->toArray()
        );
    }
}
