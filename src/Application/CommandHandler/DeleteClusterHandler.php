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
            $this->logger->warning(sprintf('Cluster does not exist with id [%s]]', (string) $message->clusterId));

            return;
        }

        if ($cluster->hasPublishedPosts()) {
            $this->logger->warning(sprintf('Cluster have published posts and cant be deleted.', (string) $message->clusterId));

            return;
        }

        $this->clusterRepository->delete($cluster);
    }
}
