<?php

namespace App\EventListener;

use App\Application\Command\PublishCluster;
use App\Application\Command\ValidateCluster;
use App\Entity\Post\Cluster;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postPersist)]
final class ClusterEvent
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $cluster = $postPersistEventArgs->getObject();
        if (!$cluster instanceof Cluster) {
            return;
        }

        $this->messageBus->dispatch(new ValidateCluster(clusterId: $cluster->getId()));

        $date = new \DateTime();

        if (null === $cluster->getProgrammedAt() || $date >= $cluster->getProgrammedAt()) {
            $this->messageBus->dispatch(new PublishCluster(clusterId: $cluster->getId()), [
                new AmqpStamp('async-medium'),
            ]);

            return;
        }
    }
}
