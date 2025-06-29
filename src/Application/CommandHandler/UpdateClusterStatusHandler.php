<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UpdateClusterStatus;
use App\Entity\Post\Cluster;
use App\Enum\ClusterStatus;
use App\Repository\Post\ClusterRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class UpdateClusterStatusHandler
{
    public function __construct(
        private ClusterRepository $clusterRepository,
    ) {
    }

    public function __invoke(UpdateClusterStatus $message): void
    {
        /** @var ?Cluster $cluster */
        $cluster = $this->clusterRepository->findOneBy(['id' => (string) $message->clusterId]);

        if (null === $cluster) {
            throw new \Exception(sprintf('Cluster does not exist with id [%s]', (string) $message->clusterId));
        }

        $cluster = $this->updateStatus($cluster);
        $this->clusterRepository->save($cluster);
    }

    private function updateStatus(Cluster $cluster): Cluster
    {
        if ($cluster->hasDraftPosts()) {
            return $cluster->setStatus(ClusterStatus::PENDING->value);
        }

        if ($cluster->hasErrorPosts() && $cluster->hasPublishedPosts()) {
            return $cluster->setStatus(ClusterStatus::PARTIAL_ERROR->value);
        }

        if ($cluster->hasErrorPosts()) {
            return $cluster->setStatus(ClusterStatus::ERROR->value);
        }

        if ($cluster->hasPublishedPosts()) {
            return $cluster->setStatus(ClusterStatus::PUBLISHED->value);
        }

        return $cluster;
    }
}
