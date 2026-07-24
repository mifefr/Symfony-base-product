<?php

namespace App\Tests\Unit\Infrastructure\EventListener;

use App\Domain\Exception\PaymentProviderException;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\EventListener\ApiExceptionListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ApiExceptionListenerTest extends TestCase
{
    private function dispatch(\Throwable $throwable): ExceptionEvent
    {
        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            $throwable
        );

        (new ApiExceptionListener())($event);

        return $event;
    }

    public function testProductNotFoundIsMappedTo404(): void
    {
        $id = ProductId::generate();
        $event = $this->dispatch(ProductNotFoundException::withId($id));

        $this->assertSame(404, $event->getResponse()->getStatusCode());
        $this->assertStringContainsString((string) $id, $event->getResponse()->getContent());
    }

    public function testPaymentProviderExceptionIsMappedTo502WithGenericMessage(): void
    {
        $event = $this->dispatch(new PaymentProviderException('internal stripe details'));

        $this->assertSame(502, $event->getResponse()->getStatusCode());
        $this->assertSame(['error' => 'Payment provider error'], json_decode($event->getResponse()->getContent(), true));
        $this->assertStringNotContainsString('stripe', $event->getResponse()->getContent());
    }

    public function testInvalidArgumentIsMappedTo400(): void
    {
        $event = $this->dispatch(new \InvalidArgumentException('Product name cannot be empty'));

        $this->assertSame(400, $event->getResponse()->getStatusCode());
        $this->assertSame(['error' => 'Product name cannot be empty'], json_decode($event->getResponse()->getContent(), true));
    }

    public function testOtherExceptionsAreLeftToTheDefaultHandler(): void
    {
        $event = $this->dispatch(new \RuntimeException('boom'));

        $this->assertNull($event->getResponse());
    }
}
