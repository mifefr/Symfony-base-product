<?php

namespace Tests\Unit\Infrastructure\Adapter;

use PHPUnit\Framework\TestCase;
use App\Infrastructure\Adapter\StripeAdapter;
use App\Domain\Model\Payment;
use Stripe\Exception\InvalidRequestException;
use Stripe\StripeClient;
use Stripe\PaymentIntent;
use Stripe\Service\PaymentIntentService;

class StripeAdapterTest extends TestCase
{
    private $stripeAdapter;
    private $stripeClient;
    private $paymentIntentService;

    protected function setUp(): void
    {
        $this->stripeClient = $this->createMock(StripeClient::class);
        $this->paymentIntentService = $this->createMock(PaymentIntentService::class);
        $this->stripeClient->paymentIntents = $this->paymentIntentService;
        
        $this->stripeAdapter = new StripeAdapter('test_key');
        $reflection = new \ReflectionClass($this->stripeAdapter);
        $property = $reflection->getProperty('stripe');
        $property->setAccessible(true);
        $property->setValue($this->stripeAdapter, $this->stripeClient);
    }

    public function testCreatePaymentIntent(): void
    {
        $paymentIntent = new PaymentIntent('pi_123');
        $paymentIntent->client_secret = 'secret_123';
        $paymentIntent->status = 'requires_payment_method';
        
        $this->paymentIntentService
            ->expects($this->once())
            ->method('create')
            ->with([
                'amount' => 100000, // 1000.00 EUR in cents
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
            ])
            ->willReturn($paymentIntent);
        
        $payment = $this->stripeAdapter->createPaymentIntent(1000.0);
        
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals('pi_123', $payment->getId());
        $this->assertEquals(1000.0, $payment->getAmount());
        $this->assertEquals('eur', $payment->getCurrency());
        $this->assertEquals('requires_payment_method', $payment->getStatus());
        $this->assertEquals('secret_123', $payment->getClientSecret());
    }

    public function testCreatePaymentIntentWithZeroAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');
        $this->stripeAdapter->createPaymentIntent(0.0);
    }

    public function testCreatePaymentIntentWithNegativeAmount(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be greater than 0');
        $this->stripeAdapter->createPaymentIntent(-100.0);
    }

    public function testCreatePaymentIntentWithStripeError(): void
    {
        $this->paymentIntentService
            ->expects($this->once())
            ->method('create')
            ->willThrowException(new \RuntimeException('Stripe API Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Stripe API Error');
        $this->stripeAdapter->createPaymentIntent(1000.0);
    }

    public function testCreatePaymentIntentWithDecimalAmount(): void
    {
        $paymentIntent = new PaymentIntent('pi_123');
        $paymentIntent->client_secret = 'secret_123';
        $paymentIntent->status = 'requires_payment_method';
        
        $this->paymentIntentService
            ->expects($this->once())
            ->method('create')
            ->with([
                'amount' => 9999, // 99.99 EUR in cents
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
            ])
            ->willReturn($paymentIntent);
        
        $payment = $this->stripeAdapter->createPaymentIntent(99.99);
        
        $this->assertInstanceOf(Payment::class, $payment);
        $this->assertEquals(99.99, $payment->getAmount());
    }
}
