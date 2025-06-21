<?php

namespace App\Denormalizer;

use ApiPlatform\Validator\Exception\ValidationException;
use App\Dto\Token\AccessToken\AbstractAccessToken;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TokenDenormalizer
{
    public function __construct(
        private DenormalizerInterface $denormalizer,
        private ValidatorInterface $validator,
    ) {
    }

    public function denormalize(array $data, string $type): AbstractAccessToken
    {
        $token = $this->denormalizer->denormalize($data, $type);

        if (!$token instanceof AbstractAccessToken) {
            throw new \InvalidArgumentException(sprintf('Expected instance of %s, got %s', AbstractAccessToken::class, get_class($token)));
        }

        $violations = $this->validator->validate($token);
        if (count($violations) > 0) {
            throw new ValidationException($violations);
        }

        return $token;
    }
}
