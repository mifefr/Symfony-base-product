<?php

namespace App\Tests\Unit\Infrastructure\Adapter;

use App\Infrastructure\Adapter\StripeAdapter;
use PHPUnit\Framework\TestCase;
use Stripe\PaymentIntent;
use Stripe\StripeClient;
use Mockery;

class StripeAdapterTest extends TestCase
{
    private $stripeClient;
    private $stripeAdapter;
    private $paymentIntents;

    protected function setUp(): void
    {
        $this->paymentIntents = Mockery::mock('Stripe\Service\PaymentIntentService');
        $this->stripeClient = Mockery::mock(StripeClient::class);
        $this->stripeClient->paymentIntents = $this->paymentIntents;
        
        $this->stripeAdapter = new StripeAdapter('fake_key');
        $this->setProtectedProperty($this->stripeAdapter, 'stripe', $this->stripeClient);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testCreatePaymentIntent(): void
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->id = 'pi_123';
        $paymentIntent->status = 'requires_payment_method';
        $paymentIntent->client_secret = 'secret_123';

        $this->paymentIntents
            ->shouldReceive('create')
            ->with([
                'amount' => 10000,
                'currency' => 'eur',
                'automatic_payment_methods' => ['enabled' => true],
            ])
            ->andReturn($paymentIntent);

        $payment = $this->stripeAdapter->createPaymentIntent(100.00);

        $this->assertEquals('pi_123', $payment->getId());
        $this->assertEquals(100.00, $payment->getAmount());
        $this->assertEquals('requires_payment_method', $payment->getStatus());
        $this->assertEquals('secret_123', $payment->getClientSecret());
    }

    public function testGetPaymentStatus(): void
    {
        $paymentIntent = new PaymentIntent();
        $paymentIntent->status = 'succeeded';

        $this->paymentIntents
            ->shouldReceive('retrieve')
            ->with('pi_123')
            ->andReturn($paymentIntent);

        $status = $this->stripeAdapter->getPaymentStatus('pi_123');

        $this->assertEquals('succeeded', $status);
    }

    private function setProtectedProperty($object, $property, $value)
    {
        $reflection = new \ReflectionClass(get_class($object));
        $property = $reflection->getProperty($property);
        $property->setAccessible(true);
        $property->setValue($object, $value);
    }
}
