<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CleanPost;
use App\Application\Command\RemoveMediaPost;
use App\Application\Command\UpdateMediaPostStatus;
use App\Application\Command\UpdateUser;
use App\Entity\Post\Post;
use App\Entity\User;
use App\Enum\MediaStatus;
use App\Repository\GroupRepository;
use App\Repository\Post\PostRepository;
use App\Repository\UserRepository;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

#[AsMessageHandler]
final class UpdateUserHandler
{
    public function __construct(
        private UserRepository $userRepository,
        private GroupRepository $groupRepository,
        private LoggerInterface $logger,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(UpdateUser $message): void
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy([
            'id' => (string) $message->userId,
        ]);

        if (null === $user) {
            $this->logger->warning(sprintf('Failed to load user: user with id [%s] was not found.', (string) $message->userId), ['id' => (string) $message->userId]);

            return;
        }
        
        if (null !== $message->patchUser->firstname) {
            $user->setFirstname($message->patchUser->firstname);
        }

        if (null !== $message->patchUser->lastname) {
            $user->setLastname($message->patchUser->lastname);
        }

        if ($message->patchUser->activeGroupId) {
            $group = $this->groupRepository->find($message->patchUser->activeGroupId);
            if ($user->isMemberOfGroup($group)) {
                $user->setActiveGroup($group);
            }
        }

        $this->userRepository->save($user);
    }
}
