<?php

namespace App\Tests\Unit\Infrastructure\Controller;

use App\Domain\Model\Payment;
use App\Domain\ValueObject\Money;
use App\Domain\Port\PaymentServiceInterface;
use App\Infrastructure\Controller\PaymentController;
use App\Infrastructure\Request\CreatePaymentRequest;
use App\Tests\Unit\Infrastructure\Controller\AbstractControllerTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class PaymentControllerTest extends AbstractControllerTestCase
{
    private PaymentController $controller;
    private PaymentServiceInterface $paymentService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentService = $this->createMock(PaymentServiceInterface::class);
        $this->controller = new PaymentController($this->paymentService);
        $this->setContainer($this->controller);
    }

    public function testCreatePayment(): void
    {
        $request = new CreatePaymentRequest();
        $request->amount = 1000.0;

        $payment = new Payment(
            'pi_123',
            new Money(100000),
            'requires_payment_method',
            'secret_123'
        );

        $this->paymentService
            ->expects($this->once())
            ->method('createPaymentIntent')
            ->with(new Money(100000))
            ->willReturn($payment);

        $response = $this->controller->createPayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('secret_123', $responseData['clientSecret']);
        $this->assertEquals('pi_123', $responseData['paymentId']);
    }

    public function testCreatePaymentWithInvalidAmount(): void
    {
        $request = new CreatePaymentRequest();
        $request->amount = -100.0;

        $response = $this->controller->createPayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', json_decode($response->getContent(), true));
    }

    public function testCreatePaymentWithProviderFailureReturnsGenericError(): void
    {
        $request = new CreatePaymentRequest();
        $request->amount = 100.0;

        $this->paymentService
            ->expects($this->once())
            ->method('createPaymentIntent')
            ->willThrowException(new \RuntimeException('Failed to create payment intent: internal stripe details'));

        $response = $this->controller->createPayment($request);

        $this->assertEquals(502, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Payment provider error', $responseData['error']);
        $this->assertStringNotContainsString('stripe', $response->getContent());
    }

    public function testGetPaymentStatus(): void
    {
        $this->paymentService
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->with('pi_123')
            ->willReturn('succeeded');

        $response = $this->controller->getPaymentStatus('pi_123');

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('succeeded', json_decode($response->getContent(), true)['status']);
    }

    public function testGetPaymentStatusWithProviderFailureReturnsGenericError(): void
    {
        $this->paymentService
            ->expects($this->once())
            ->method('getPaymentStatus')
            ->willThrowException(new \RuntimeException('Failed to get payment status: internal stripe details'));

        $response = $this->controller->getPaymentStatus('pi_123');

        $this->assertEquals(502, $response->getStatusCode());
        $this->assertEquals('Payment provider error', json_decode($response->getContent(), true)['error']);
    }
}
