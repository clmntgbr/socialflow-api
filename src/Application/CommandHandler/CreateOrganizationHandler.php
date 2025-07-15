<?php

namespace App\Application\CommandHandler;

use App\Application\Command\CreateOrganization;
use App\Entity\Organization;
use App\Entity\User;
use App\Exception\UserNotFoundException;
use App\Repository\UserRepository;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class CreateOrganizationHandler
{
    public function __construct(
        private UserRepository $userRepository,
    ) {
    }

    public function __invoke(CreateOrganization $message): void
    {
        /** @var ?User $user */
        $user = $this->userRepository->findOneBy(['id' => (string) $message->userId]);

        if (null === $user) {
            throw new UserNotFoundException((string) $message->userId);
        }

        $organization = (new Organization())
            ->addMember($user)
            ->setName($user->getFirstname() . "'s organization")
            ->setAdmin($user);

        $user->setActiveOrganization($organization);
        $user->addOrganization($organization);

        $this->userRepository->save($user, true);
    }
}
