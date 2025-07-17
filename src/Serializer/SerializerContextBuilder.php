<?php

namespace App\Serializer;

use ApiPlatform\State\SerializerContextBuilderInterface;
use App\Entity\Group;
use App\Entity\User;
use App\Service\ContextService;
use Symfony\Component\HttpFoundation\Request;

class SerializerContextBuilder implements SerializerContextBuilderInterface
{
    private array $disabledEntity = [];

    public function __construct(
        private readonly SerializerContextBuilderInterface $serializerContextBuilder,
        private readonly ContextService $contextService,
    ) {
    }

    public function createFromRequest(Request $request, bool $normalization, ?array $extractedAttributes = null): array
    {
        $context = $this->serializerContextBuilder->createFromRequest($request, $normalization, $extractedAttributes);
        $resourceClass = $context['resource_class'] ?? null;

        if (in_array($resourceClass, $this->disabledEntity, true)) {
            return $context;
        }

        $data = $request->query->get('serializer', 'none');
        $groups = $this->contextService->getGroups($data);

        if (is_null($groups)) {
            return $context;
        }

        $context['groups'] = $groups;

        return $context;
    }
}
