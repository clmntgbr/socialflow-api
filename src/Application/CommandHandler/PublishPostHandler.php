<?php

namespace App\Application\CommandHandler;

use App\Application\Command\PublishPost;
use App\Application\Command\UpdateClusterStatus;
use App\Entity\Post\Post;
use App\Repository\Post\PostRepository;
use App\Service\Publish\PublishServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class PublishPostHandler
{
    public function __construct(
        private PostRepository $postRepository,
        private PublishServiceFactory $publishServiceFactory,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(PublishPost $message): void
    {
        /** @var ?Post $post */
        $post = $this->postRepository->findOneBy(['id' => (string) $message->postId]);

        if (null === $post) {
            $this->logger->info('Post does not exist', ['id' => (string) $message->postId]);

            return;
        }

        $socialAccount = $post->getCluster()->getSocialAccount();

        if (!$socialAccount->isActive()) {
            $this->logger->info('This social account cant publish', ['id' => (string) $socialAccount->getId(), 'status' => $socialAccount->getStatus()]);

            return;
        }

        if ($post->isPublished()) {
            $this->logger->info('This post is already published', ['id' => (string) $post->getId(), 'status' => $post->getStatus()]);

            return;
        }

        try {
            $service = $this->publishServiceFactory->get($socialAccount->getType());

            $getPost = $service->post($post);
            $post->setPublished($getPost->getId());
        } catch (\Exception $exception) {
            $this->logger->alert($exception->getMessage(), ['postId' => (string) $post->getId()]);
            $post->setFailed();
        }

        $this->postRepository->save($post);

        $this->messageBus->dispatch(new UpdateClusterStatus(clusterId: $post->getCluster()->getId()), [
            new AmqpStamp('async-medium'),
        ]);
    }
}
