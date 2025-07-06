<?php

namespace App\EventListener;

use App\Application\Command\DeleteCluster;
use App\Application\Command\DeletePost;
use App\Entity\Post\Post;
use App\Exception\PublishException;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postRemove)]
final class PostEvent
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $preRemoveEventArgs): void
    {
        $post = $preRemoveEventArgs->getObject();
        if (!$post instanceof Post) {
            return;
        }

        try {
            $this->messageBus->dispatch(new DeletePost(postId: $post->getId()), [
                new AmqpStamp('sync'),
            ]);
        } catch (HandlerFailedException $exception) {
            $originalException = $exception->getPrevious();
            if ($originalException instanceof PublishException) {
                throw new PublishException(message: $originalException->getMessage());
            }

            throw $exception;
        } catch (\Exception $exception) {
            throw new PublishException(message: $exception->getMessage());
        }
    }

    public function postRemove(PostRemoveEventArgs $postRemoveEventArgs): void
    {
        $post = $postRemoveEventArgs->getObject();
        if (!$post instanceof Post) {
            return;
        }

        $this->messageBus->dispatch(new DeleteCluster(clusterId: $post->getCluster()->getId()), [
            new AmqpStamp('async-low'),
        ]);
    }
}
