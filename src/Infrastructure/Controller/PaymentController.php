<?php

declare(strict_types=1);

namespace App\Infrastructure\Primary\Controller;

use App\Domain\Port\PaymentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    #[Route('/api/payment/create', name: 'create_payment', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $amount = $data['amount'] ?? 0;

        $payment = $this->paymentService->createPaymentIntent($amount);

        return $this->json([
            'clientSecret' => $payment->getClientSecret(),
            'paymentId' => $payment->getId()
        ]);
    }

    #[Route('/api/payment/{paymentId}/status', name: 'payment_status', methods: ['GET'])]
    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        $status = $this->paymentService->getPaymentStatus($paymentId);

        return $this->json(['status' => $status]);
    }
}
