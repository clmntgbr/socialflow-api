<?php

namespace App\EventListener;

use App\Application\Command\SocialAccountOnActivation;
use App\Application\Command\SocialAccountOnDelete;
use App\Entity\SocialAccount\SocialAccount;
use App\Enum\SocialAccountStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::postRemove)]
#[AsDoctrineListener(event: Events::postUpdate)]
final class SocialAccountListener
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function postRemove(PostRemoveEventArgs $args): void
    {
        $socialAccount = $args->getObject();

        if (!is_a($socialAccount, SocialAccount::class)) {
            return;
        }

        $this->messageBus->dispatch(new SocialAccountOnDelete(socialAccountId: $socialAccount->getId()), [
            new AmqpStamp('async-medium'),
        ]);
    }

    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $socialAccount = $args->getObject();

        if (!is_a($socialAccount, SocialAccount::class)) {
            return;
        }

        if ($socialAccount->getStatus() === SocialAccountStatus::ACTIVE->value) {
            $this->messageBus->dispatch(new SocialAccountOnActivation(socialAccountId: $socialAccount->getId()), [
                new AmqpStamp('async-medium'),
            ]);
        }
    }
}
