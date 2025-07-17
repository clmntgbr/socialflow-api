<?php

namespace App\EventListener;

use App\Application\Command\CreateGroup;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[AsDoctrineListener(event: Events::prePersist)]
#[AsDoctrineListener(event: Events::preUpdate)]
#[AsDoctrineListener(event: Events::postPersist)]
final class UserListener
{
    public function __construct(
        private UserPasswordHasherInterface $userPasswordHasher,
        private MessageBusInterface $bus,
    ) {
    }

    public function prePersist(PrePersistEventArgs $prePersistEventArgs): void
    {
        $entity = $prePersistEventArgs->getObject();
        if (!$entity instanceof User) {
            return;
        }

        $this->hashPassword($entity);
    }

    public function postPersist(PostPersistEventArgs $postPersistEventArgs): void
    {
        $entity = $postPersistEventArgs->getObject();
        if (!$entity instanceof User) {
            return;
        }

        $this->createGroup($entity);
    }

    public function preUpdate(PreUpdateEventArgs $preUpdateEventArgs): void
    {
        $entity = $preUpdateEventArgs->getObject();
        if (!$entity instanceof User) {
            return;
        }

        $this->hashPassword($entity);
    }

    private function hashPassword(User $user): void
    {
        if (null !== $user->getPlainPassword()) {
            $password = $this->userPasswordHasher->hashPassword($user, $user->getPlainPassword());
            $user->setPassword($password);
        }

        $user->eraseCredentials();
    }

    private function createGroup(User $user): void
    {
        $this->bus->dispatch(new CreateGroup(
            userId: $user->getId(),
        ));
    }
}
