<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Port\PaymentServiceInterface;
use App\Domain\ValueObject\Money;
use App\Infrastructure\Request\CreatePaymentRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    #[Route('/api/payment/create', name: 'create_payment', methods: ['POST'])]
    public function createPayment(#[MapRequestPayload] CreatePaymentRequest $request): JsonResponse
    {
        try {
            $payment = $this->paymentService->createPaymentIntent(
                Money::fromDecimal($request->amount)
            );

            return $this->json([
                'clientSecret' => $payment->getClientSecret(),
                'paymentId' => $payment->getId()
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Payment provider error'], Response::HTTP_BAD_GATEWAY);
        }
    }

    #[Route('/api/payment/{paymentId}/status', name: 'payment_status', methods: ['GET'])]
    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        try {
            $status = $this->paymentService->getPaymentStatus($paymentId);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => 'Payment provider error'], Response::HTTP_BAD_GATEWAY);
        }

        return $this->json(['status' => $status]);
    }
}
