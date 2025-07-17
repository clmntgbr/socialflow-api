<?php

namespace App\ApiResource;

use App\Application\Command\UpdateUser;
use App\Application\Command\UpdateUserName;
use App\Dto\Context;
use App\Dto\User\PatchUser;
use App\Dto\User\PatchUserName;
use App\Entity\User;
use App\Repository\GroupRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class PatchUserNameController
{
    public function __construct(
        private Security $security,
        private readonly SerializerInterface $serializer,
        private ContextService $contextService,
        private GroupRepository $groupRepository,
        private UserRepository $userRepository,
        private MessageBusInterface $messageBus,
    ) {
    }

    public function __invoke(
        #[MapRequestPayload] PatchUserName $data,
        Context $context,
    ): JsonResponse {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User not authenticated.');
        }

        $this->messageBus->dispatch(new UpdateUserName(
            userId: $user->getId(),
            patchUserName: $data
        ));

        return new JsonResponse(
            data: $this->serializer->serialize($user, 'json', $context->getGroups()),
            status: Response::HTTP_OK,
            json: true
        );
    }
}
