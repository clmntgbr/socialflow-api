<?php

namespace App\EventListener;

use App\Entity\SocialAccount\SocialAccount;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::preRemove)]
final class SocialAccountEvent
{
    public function __construct(
        private MessageBusInterface $messageBus,
    ) {
    }

    public function preRemove(PreRemoveEventArgs $preRemoveEventArgs): void
    {
        $socialAccount = $preRemoveEventArgs->getObject();

        if (!is_a($socialAccount, SocialAccount::class)) {
            return;
        }
    }
}
