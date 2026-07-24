<?php

declare(strict_types=1);

namespace App\Infrastructure\Bus;

use App\Application\Bus\CommandBusInterface;
use App\Application\Command\CommandInterface;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final class MessengerCommandBus implements CommandBusInterface
{
    public function __construct(
        private readonly MessageBusInterface $commandBus
    ) {
    }

    public function dispatch(CommandInterface $command): void
    {
        try {
            $this->commandBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            throw $e->getPrevious() ?? $e;
        }
    }
}
