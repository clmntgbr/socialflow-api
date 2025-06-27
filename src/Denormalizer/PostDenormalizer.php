<?php

namespace App\Denormalizer;

use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class PostDenormalizer
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    public function denormalize($data, string $type, array $context = [])
    {
        return array_map(
            fn ($datum) => $this->denormalizer->denormalize($datum, $type)?->setCluster($context['cluster'] ?? null),
            $data
        );
    }
}
