<?php

namespace App\EventListener;

use App\Application\Command\SocialAccountOnActivation;
use App\Application\Command\SocialAccountOnDelete;
use App\Entity\SocialAccount\SocialAccount;
use App\Enum\SocialAccountStatus;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::preUpdate)]
final class SocialAccountListener
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

        $this->messageBus->dispatch(new SocialAccountOnDelete(socialAccountId: $socialAccount->getId()), [
            new AmqpStamp('async-medium'),
        ]);
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $socialAccount = $preUpdateEventArgs->getObject();

        if (!is_a($socialAccount, SocialAccount::class)) {
            return;
        }
        if ($preUpdateEventArgs->hasChangedField('status')) {
            $oldStatus = $preUpdateEventArgs->getOldValue('status');
            $newStatus = $preUpdateEventArgs->getNewValue('status');

            if ($oldStatus !== SocialAccountStatus::ACTIVE->value && $newStatus === SocialAccountStatus::ACTIVE->value) {
                $this->messageBus->dispatch(new SocialAccountOnActivation(socialAccountId: $socialAccount->getId()), [
                    new AmqpStamp('async-medium'),
                ]);
            }
        }
    }
}
