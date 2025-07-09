<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ContentValidationException extends HttpException
{
    private string $postOrder;
    private ?string $mediaPostOrder;

    public function __construct(string $message, string $postOrder, ?string $mediaPostOrder = null, int $code = Response::HTTP_BAD_REQUEST, ?\Throwable $previous = null)
    {
        parent::__construct(statusCode: $code, message: $message, previous: $previous);
        $this->postOrder = $postOrder;
        $this->mediaPostOrder = $mediaPostOrder;
    }

    public function getPostOrder(): string
    {
        return $this->postOrder;
    }

    public function getMediaPostOrder(): ?string
    {
        return $this->mediaPostOrder;
    }
}
