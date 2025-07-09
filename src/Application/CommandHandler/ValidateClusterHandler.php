<?php

namespace App\Application\CommandHandler;

use App\Application\Command\ValidateCluster;
use App\Application\Command\ValidatePost;
use App\Entity\Post\Cluster;
use App\Entity\Post\Post;
use App\Repository\Post\ClusterRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class ValidateClusterHandler
{
    public function __construct(
        private ClusterRepository $clusterRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(ValidateCluster $message): void
    {
        /** @var ?Cluster $cluster */
        $cluster = $this->clusterRepository->findOneBy([
            'id' => (string) $message->clusterId,
        ]);

        if (null === $cluster) {
            $this->logger->warning(sprintf('Failed to delete cluster: cluster with id [%s] was not found.', (string) $message->clusterId), ['id' => (string) $message->clusterId]);

            return;
        }

        array_map(
            fn (Post $post) => $this->messageBus->dispatch(
                new ValidatePost(postId: $post->getId())
            ),
            $cluster->getPosts()->toArray()
        );
    }
}
