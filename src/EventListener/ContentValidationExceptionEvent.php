<?php

namespace App\EventListener;

use App\Exception\ContentValidationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ContentValidationExceptionEvent
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof ContentValidationException) {
            return;
        }

        $statusCode = $exception instanceof HttpExceptionInterface
            ? $exception->getStatusCode()
            : JsonResponse::HTTP_BAD_REQUEST;

        $response = new JsonResponse([
            'title' => 'An error occurred',
            'detail' => $exception->getMessage(),
            'status' => $statusCode,
            'postOrder' => $exception->getPostOrder(),
            'mediaPostOrder' => $exception->getMediaPostOrder(),
        ], $statusCode);

        $event->setResponse($response);
    }
}
