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
     * @return T
     *
     * @throws \InvalidArgumentException
     * @throws ValidationException
     */
    public function denormalize(array $data, string $type): object
    {
        if (!class_exists($type) && !interface_exists($type)) {
            throw new \InvalidArgumentException(sprintf('Class or interface %s does not exist', $type));
        }

        $object = $this->denormalizer->denormalize($data, $type);

        if (!is_object($object)) {
            throw new \InvalidArgumentException(sprintf('Denormalizer returned %s instead of object', gettype($object)));
        }

        if (!is_a($object, $type)) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', $type, get_class($object)));
        }

        $violations = $this->validator->validate($object);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        /* @var T $object */
        return $object;
    }
}
