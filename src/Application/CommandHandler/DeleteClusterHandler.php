<?php

namespace App\Application\CommandHandler;

use App\Application\Command\DeleteCluster;
use App\Entity\Post\Cluster;
use App\Repository\Post\ClusterRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class DeleteClusterHandler
{
    public function __construct(
        private ClusterRepository $clusterRepository,
        private LoggerInterface $logger,
    ) {
    }

    public function __invoke(DeleteCluster $message): void
    {
        /** @var ?Cluster $cluster */
        $cluster = $this->clusterRepository->findOneBy([
            'id' => (string) $message->clusterId,
        ]);

        if (null === $cluster) {
            $this->logger->warning(sprintf('Failed to delete cluster: cluster with id [%s] was not found.', (string) $message->clusterId), ['id' => (string) $message->clusterId]);

            return;
        }

        if ($cluster->hasPublishedPosts()) {
            $this->logger->warning(sprintf('Cannot delete cluster with id [%s]: cluster has published posts.', (string) $message->clusterId), ['id' => (string) $message->clusterId]);

            return;
        }

        $this->clusterRepository->delete($cluster);
    }
}
