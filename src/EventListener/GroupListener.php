<?php

namespace App\EventListener;

use App\Application\Command\PublishCluster;
use App\Application\Command\ValidateCluster;
use App\Entity\Group;
use App\Entity\Post\Cluster;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postLoad)]
final class GroupListener
{
    public function __construct(
        private Security $security
    ) {
    }

    public function postLoad(PostLoadEventArgs $postLoadEventArgs): void
    {
        $group = $postLoadEventArgs->getObject();
        if (!$group instanceof Group) {
            return;
        }

        /** @var User $user */
        $user = $this->security->getUser();

        if (null === $user) {
            return;
        }

        if ((string) $group->getAdmin()->getId() === (string) $user->getId()) {
            $group->markAsAdmin();
        }
    }
}
