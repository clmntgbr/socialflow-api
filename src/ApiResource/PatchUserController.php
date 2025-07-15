<?php

namespace App\ApiResource;

use App\Application\Command\UpdateUser;
use App\Dto\User\PatchUser;
use App\Entity\Organization;
use App\Entity\User;
use App\Repository\OrganizationRepository;
use App\Repository\UserRepository;
use App\Service\ContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class PatchUserController
{
    public function __construct(
        private Security $security,
        private readonly SerializerInterface $serializer,
        private ContextService $contextService,
        private OrganizationRepository $organizationRepository,
        private UserRepository $userRepository,
        private MessageBusInterface $messageBus
    ) {}

    public function __invoke(
        #[MapRequestPayload] PatchUser $data,
        Request $request
    ): JsonResponse {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User not authenticated.');
        }
        
        $this->messageBus->dispatch(new UpdateUser(
            userId: $user->getId(),
            patchUser: $data
        ));

        $groupsParam = $request->query->get('groups', 'user.read');
        $groups = $this->contextService->getGroups($groupsParam);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups($groups)
            ->toArray();

        return new JsonResponse(
            data: $this->serializer->serialize($user, 'json', $context),
            status: Response::HTTP_OK,
            json: true
        );
    }
}
