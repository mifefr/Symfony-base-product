<?php

namespace Tests\Unit\Infrastructure\Controller;

use App\Domain\Model\Payment;
use App\Domain\Port\PaymentServiceInterface;
use App\Infrastructure\Controller\PaymentController;
use Tests\Unit\Infrastructure\Controller\AbstractControllerTest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

class PaymentControllerTest extends AbstractControllerTest
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
        $amount = 1000.0;
        $request = new Request([], [], [], [], [], [], json_encode([
            'amount' => $amount
        ]));

        $payment = new Payment(
            'pi_123',
            $amount,
            'eur',
            'secret_123',
            'requires_payment_method'
        );

        $this->paymentService
            ->expects($this->once())
            ->method('createPaymentIntent')
            ->with($amount)
            ->willReturn($payment);

        $response = $this->controller->createPayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('requires_payment_method', $responseData['clientSecret']);
        $this->assertEquals('pi_123', $responseData['paymentId']);
    }

    public function testCreatePaymentWithInvalidAmount(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'amount' => -100.0
        ]));

        $response = $this->controller->createPayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', json_decode($response->getContent(), true));
    }

    public function testCreatePaymentWithMissingAmount(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([]));

        $response = $this->controller->createPayment($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(400, $response->getStatusCode());
        $this->assertArrayHasKey('error', json_decode($response->getContent(), true));
    }
}
