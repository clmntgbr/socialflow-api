<?php

namespace App\Messenger\Middleware;

use App\Exception\ContentValidationException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Middleware\MiddlewareInterface;
use Symfony\Component\Messenger\Middleware\StackInterface;

class UnwrapExceptionMiddleware implements MiddlewareInterface
{
    public function handle(Envelope $envelope, StackInterface $stack): Envelope
    {
        try {
            return $stack->next()->handle($envelope, $stack);
        } catch (\Exception $exception) {
            $current = $exception;
            while ($current) {
                if ($current instanceof ContentValidationException) {
                    throw $current;
                }
                $current = $current->getPrevious();
            }
            throw $exception;
        }
    }
}
