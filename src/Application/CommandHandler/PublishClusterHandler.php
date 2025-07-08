<?php

namespace App\Application\CommandHandler;

use App\Application\Command\PublishCluster;
use App\Application\Command\PublishPost;
use App\Entity\Post\Cluster;
use App\Repository\Post\ClusterRepository;
use App\Service\Publish\PublishServiceFactory;
use App\Service\SocialAccount\SocialAccountServiceFactory;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class PublishClusterHandler
{
    public function __construct(
        private ClusterRepository $clusterRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
        private PublishServiceFactory $publishServiceFactory,
        private SocialAccountServiceFactory $socialAccountServiceFactory,
    ) {
    }

    public function __invoke(PublishCluster $message): void
    {
        /** @var ?Cluster $cluster */
        $cluster = $this->clusterRepository->findOneBy(['id' => (string) $message->clusterId]);

        if (null === $cluster) {
            $this->logger->info(sprintf('Cannot publish cluster: cluster with id [%s] was not found.', (string) $message->clusterId), ['id' => (string) $message->clusterId]);

            return;
        }

        $socialAccount = $cluster->getSocialAccount();

        foreach ($cluster->getPosts() as $post) {
            $this->messageBus->dispatch(new PublishPost(postId: $post->getId()), [
                new AmqpStamp('async-medium'),
            ]);
        }
    }
}
