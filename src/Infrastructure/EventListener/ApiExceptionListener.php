<?php

declare(strict_types=1);

namespace App\Infrastructure\EventListener;

use App\Domain\Exception\PaymentProviderException;
use App\Domain\Exception\ProductNotFoundException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

#[AsEventListener]
final class ApiExceptionListener
{
    public function __invoke(ExceptionEvent $event): void
    {
        $throwable = $event->getThrowable();

        [$statusCode, $message] = match (true) {
            $throwable instanceof ProductNotFoundException => [Response::HTTP_NOT_FOUND, $throwable->getMessage()],
            $throwable instanceof PaymentProviderException => [Response::HTTP_BAD_GATEWAY, 'Payment provider error'],
            $throwable instanceof \InvalidArgumentException => [Response::HTTP_BAD_REQUEST, $throwable->getMessage()],
            default => [null, null],
        };

        if ($statusCode !== null) {
            $event->setResponse(new JsonResponse(['error' => $message], $statusCode));
        }
    }
}
