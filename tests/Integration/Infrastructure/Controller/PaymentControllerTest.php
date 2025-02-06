<?php

namespace Tests\Integration\Infrastructure\Controller;

use PHPUnit\Framework\TestCase;
use App\Domain\Port\PaymentServiceInterface;
use App\Domain\Model\Payment;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class TestPaymentController
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    public function createPayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;

        $payment = $this->paymentService->createPaymentIntent($amount);

        return new JsonResponse([
            'clientSecret' => $payment->getClientSecret(),
            'paymentId' => $payment->getId()
        ]);
    }
}

class PaymentControllerTest extends TestCase
{
    private $controller;
    private $paymentService;

    protected function setUp(): void
    {
        $this->paymentService = $this->createMock(PaymentServiceInterface::class);
        $this->controller = new TestPaymentController($this->paymentService);
    }

    public function testCreatePayment(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'amount' => 1000.0
        ]));

        $payment = new Payment(
            'pi_123',
            1000.0,
            'eur',
            'requires_payment_method',
            'secret_123'
        );

        $this->paymentService
            ->expects($this->once())
            ->method('createPaymentIntent')
            ->with(1000.0)
            ->willReturn($payment);

        $response = $this->controller->createPayment($request);

        $this->assertEquals(200, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('secret_123', $responseData['clientSecret']);
        $this->assertEquals('pi_123', $responseData['paymentId']);
    }
}
