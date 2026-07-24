<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Application\Bus\QueryBusInterface;
use App\Application\Query\QueryInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

final class MessengerQueryBus implements QueryBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $queryBus
    ) {
    }

    public function ask(QueryInterface $query): mixed
    {
        try {
            $envelope = $this->queryBus->dispatch($query);
        } catch (HandlerFailedException $e) {
            throw $e->getPrevious() ?? $e;
        }

        $handledStamp = $envelope->last(HandledStamp::class);
        if ($handledStamp === null) {
            throw new \LogicException(sprintf('Query "%s" was not handled.', $query::class));
        }

        return $handledStamp->getResult();
    }
}
