<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CleanPost;
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
            $this->logger->info(sprintf('Cannot publish post: post with id [%s] was not found.', (string) $message->postId), ['id' => (string) $message->postId]);

            return;
        }

        $socialAccount = $post->getCluster()->getSocialAccount();

        if (!$socialAccount->isActive()) {
            $this->logger->info(sprintf('Cannot publish post: social account with id [%s] is not active (status: %s).', (string) $socialAccount->getId(), $socialAccount->getStatus()), ['id' => (string) $socialAccount->getId(), 'status' => $socialAccount->getStatus()]);

            return;
        }

        if ($post->isPublished()) {
            $this->logger->info(sprintf('Cannot publish post: post with id [%s] is already published (status: %s).', (string) $post->getId(), $post->getStatus()), ['id' => (string) $post->getId(), 'status' => $post->getStatus()]);

            return;
        }

        try {
            $service = $this->publishServiceFactory->get($socialAccount->getType());

            $medias = $service->processMediaBatchUpload($post);
            $publishedPost = $service->post($post, $medias);
            $post->setPublished($publishedPost->getId());

            $this->messageBus->dispatch(new CleanPost(postId: $post->getId()), [
                new AmqpStamp('async-medium'),
            ]);
        } catch (\Exception $exception) {
            $this->logger->alert(sprintf('Failed to publish post with id [%s]: %s', (string) $post->getId(), $exception->getMessage()), ['postId' => (string) $post->getId()]);
            $post->setFailed();
        }

        $this->postRepository->save($post);

        $this->messageBus->dispatch(new UpdateClusterStatus(clusterId: $post->getCluster()->getId()), [
            new AmqpStamp('async-medium'),
        ]);
    }
}
