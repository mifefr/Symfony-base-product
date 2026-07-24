<?php

namespace App\Tests\Unit\Infrastructure\Bus;

use App\Application\Query\ListProducts\ListProductsQuery;
use App\Infrastructure\Bus\MessengerQueryBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class MessengerQueryBusTest extends TestCase
{
    public function testAskReturnsHandlerResult(): void
    {
        $query = new ListProductsQuery();
        $envelope = new Envelope($query, [new HandledStamp(['result'], 'handler')]);
        $messenger = $this->createMock(MessageBusInterface::class);
        $messenger->method('dispatch')->willReturn($envelope);

        $this->assertSame(['result'], (new MessengerQueryBus($messenger))->ask($query));
    }

    public function testAskThrowsWhenQueryNotHandled(): void
    {
        $query = new ListProductsQuery();
        $messenger = $this->createMock(MessageBusInterface::class);
        $messenger->method('dispatch')->willReturn(new Envelope($query));

        $this->expectException(\LogicException::class);

        (new MessengerQueryBus($messenger))->ask($query);
    }

    public function testAskUnwrapsHandlerFailedException(): void
    {
        $query = new ListProductsQuery();
        $messenger = $this->createMock(MessageBusInterface::class);
        $messenger
            ->method('dispatch')
            ->willThrowException(new HandlerFailedException(new Envelope($query), [new \RuntimeException('boom')]));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('boom');

        (new MessengerQueryBus($messenger))->ask($query);
    }
}
