<?php

declare(strict_types=1);

namespace App\EventSubscriber;

use App\Exception\DomainException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener]
final class ApiExceptionSubscriber
{
    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();
        if (!$throwable instanceof DomainException) {
            return;
        }

        $payload = [
            'error' => $throwable->errorCode,
            'message' => $throwable->getMessage(),
        ];
        if ($throwable->fields !== []) {
            $payload['fields'] = $throwable->fields;
        }

        $event->setResponse(new JsonResponse($payload, $throwable->statusCode));
    }
}
