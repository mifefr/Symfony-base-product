<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Domain\Port\PaymentServiceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Response;

class PaymentController extends AbstractController
{
    public function __construct(
        private readonly PaymentServiceInterface $paymentService
    ) {}

    #[Route('/api/payment/create', name: 'create_payment', methods: ['POST'])]
    public function createPayment(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['amount'])) {
            return new JsonResponse(['error' => 'Amount is required'], Response::HTTP_BAD_REQUEST);
        }

        $amount = (float) $data['amount'];
        if ($amount <= 0) {
            return new JsonResponse(['error' => 'Amount must be greater than 0'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $payment = $this->paymentService->createPaymentIntent($amount);

            return $this->json([
                'clientSecret' => $payment->getClientSecret(),
                'paymentId' => $payment->getId()
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        } catch (\RuntimeException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/api/payment/{paymentId}/status', name: 'payment_status', methods: ['GET'])]
    public function getPaymentStatus(string $paymentId): JsonResponse
    {
        $status = $this->paymentService->getPaymentStatus($paymentId);

        return $this->json(['status' => $status]);
    }
}
