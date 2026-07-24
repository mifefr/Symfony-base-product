<?php

namespace App\Tests\Unit\Infrastructure\Bus;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Bus\MessengerCommandBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

class MessengerCommandBusTest extends TestCase
{
    public function testDispatchForwardsToMessenger(): void
    {
        $command = new CreateProductCommand('Test', new Money(100), '');
        $messenger = $this->createMock(MessageBusInterface::class);
        $messenger
            ->expects($this->once())
            ->method('dispatch')
            ->with($command)
            ->willReturn(new Envelope($command));

        (new MessengerCommandBus($messenger))->dispatch($command);
    }

    public function testDispatchUnwrapsHandlerFailedException(): void
    {
        $command = new CreateProductCommand('Test', new Money(100), '');
        $domainException = new \InvalidArgumentException('Product name cannot be empty');
        $messenger = $this->createMock(MessageBusInterface::class);
        $messenger
            ->method('dispatch')
            ->willThrowException(new HandlerFailedException(new Envelope($command), [$domainException]));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        (new MessengerCommandBus($messenger))->dispatch($command);
    }
}
