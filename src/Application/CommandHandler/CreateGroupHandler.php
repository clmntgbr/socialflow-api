<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateGroup;
use App\Entity\Group;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateGroupHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(CreateGroup $message): void
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['id' => (string) $message->userId]);

        if (null === $user) {
            throw new UserNotFoundException((string) $message->userId);
        }

        $group = (new Group())
            ->addMember($user)
            ->setName($user->getFirstname() . "'s group")
            ->setAdmin($user);

        $user->setActiveGroup($group);
        $user->addGroup($group);

        $this->userRepository->save($user, true);
    }
}
