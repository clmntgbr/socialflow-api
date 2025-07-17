<?php

namespace App\ApiResource;

use App\Entity\Group;
use App\Entity\User;
use App\Service\ContextService;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Serializer\Attribute\Context;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

#[AsController]
class GetActiveGroupController
{
    public function __construct(
        private Security $security,
        private readonly SerializerInterface $serializer,
        private ContextService $contextService
    ) {}

    public function __invoke(Request $request): JsonResponse
    {
        $user = $this->security->getUser();

        if (!$user instanceof User) {
            throw new \RuntimeException('User not authenticated.');
        }

        $groupsParam = $request->query->get('groups', 'group.read');
        $groups = $this->contextService->getGroups($groupsParam);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups($groups)
            ->toArray();

        return new JsonResponse(
            data: $this->serializer->serialize($user->getActiveGroup(), 'json', $context),
            status: Response::HTTP_OK,
            json: true
        );
    }
}
