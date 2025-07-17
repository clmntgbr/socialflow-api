<?php

namespace App\Resolver;

use App\Dto\Context;
use App\Dto\CreateAnalysis;
use App\Service\ContextService;
use App\Service\ValidatorError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Serializer\Context\Normalizer\ObjectNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class ContextResolver implements ValueResolverInterface
{
    public function __construct(
        private SerializerInterface $serializer,
        private ContextService $contextService,
    ) {}

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        if ($argument->getType() !== Context::class) {
            return;
        }

        $groupsParam = $request->query->get('serializer', 'none');
        $groups = $this->contextService->getGroups($groupsParam);

        $context = (new ObjectNormalizerContextBuilder())
            ->withGroups($groups)
            ->toArray();

        yield new Context($context);
    }
}