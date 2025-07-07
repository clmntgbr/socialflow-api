<?php

namespace App\Serializer;

use App\Enum\SocialAccountStatus;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ValueObjectNormalizer implements NormalizerInterface
{
    public function getSupportedTypes(?string $format): array
    {
        return [
            SocialAccountStatus::class => true,
        ];
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof SocialAccountStatus;
    }

    public function normalize(mixed $data, ?string $format = null, array $context = []): array|string|int|float|bool|\ArrayObject|null
    {
        return (string) $data;
    }
}
