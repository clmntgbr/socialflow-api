<?php

namespace App\Application\CommandHandler;

use App\Application\Command\UpdateUser;
use App\Application\Command\UpdateUserActiveGroup;
use App\Application\Command\UpdateUserName;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class UpdateUserActiveGroupHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(UpdateUserActiveGroup $message): void
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy([
            'id' => (string) $message->userId,
        ]);

        if (null === $user) {
            $this->logger->warning(sprintf('Failed to load user: user with id [%s] was not found.', (string) $message->userId), ['id' => (string) $message->userId]);

            return;
        }

        if ($message->patchUserActiveGroup->activeGroupId) {
            $group = $this->groupRepository->find($message->patchUserActiveGroup->activeGroupId);
            if ($user->isMemberOfGroup($group)) {
                $user->setActiveGroup($group);
            }
        }

        $this->userRepository->save($user);
    }
}
