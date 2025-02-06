<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Model\Payment;
use App\Domain\Port\PaymentServiceInterface;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeAdapter implements PaymentServiceInterface
{
    private StripeClient $stripe;

    public function __construct(string $apiKey)
    {
        $this->stripe = new StripeClient($apiKey);
    }

    public function createPaymentIntent(float $amount, string $currency = 'eur'): Payment
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => (int) ($amount * 100), // Stripe expects amounts in cents
                'currency' => $currency,
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return new Payment(
                $paymentIntent->id,
                $amount,
                $currency,
                $paymentIntent->status,
                $paymentIntent->client_secret
            );
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to create payment intent: ' . $e->getMessage());
        }
    }

    public function confirmPayment(string $paymentIntentId): bool
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->status === 'succeeded';
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to confirm payment: ' . $e->getMessage());
        }
    }

    public function getPaymentStatus(string $paymentIntentId): string
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->status;
        } catch (ApiErrorException $e) {
            throw new \RuntimeException('Failed to get payment status: ' . $e->getMessage());
        }
    }
}
