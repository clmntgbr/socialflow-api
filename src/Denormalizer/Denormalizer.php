<?php

namespace App\Denormalizer;

use ApiPlatform\Validator\Exception\ValidationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @template T
 */
class Denormalizer
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    /**
     * @param class-string<T> $type
     *
     * @return T|array<T>
     *
     * @throws \InvalidArgumentException
     * @throws ValidationException
     */
    public function denormalize(array $data, string $type): object|array
    {
        $typeCheck = str_replace('[]', '', $type);
        if (!class_exists($typeCheck) && !interface_exists($typeCheck)) {
            throw new \InvalidArgumentException(sprintf('Class or interface %s does not exist', $typeCheck));
        }

        $object = $this->denormalizer->denormalize($data, $type);

        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $object;
    }
}
