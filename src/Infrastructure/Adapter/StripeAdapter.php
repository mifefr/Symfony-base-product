<?php

declare(strict_types=1);

namespace App\Infrastructure\Adapter;

use App\Domain\Exception\PaymentProviderException;
use App\Domain\Model\Payment;
use App\Domain\Port\PaymentServiceInterface;
use App\Domain\ValueObject\Money;
use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeAdapter implements PaymentServiceInterface
{
    private StripeClient $stripe;

    public function __construct(string $apiKey)
    {
        $this->stripe = new StripeClient($apiKey);
    }

    public function createPaymentIntent(Money $amount): Payment
    {
        if ($amount->isZero()) {
            throw new \InvalidArgumentException('Amount must be greater than 0');
        }

        try {
            $paymentIntent = $this->stripe->paymentIntents->create([
                'amount' => $amount->getAmountInCents(),
                'currency' => strtolower($amount->getCurrency()),
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return new Payment(
                $paymentIntent->id,
                $amount,
                $paymentIntent->status,
                $paymentIntent->client_secret
            );
        } catch (ApiErrorException $e) {
            throw new PaymentProviderException('Failed to create payment intent: ' . $e->getMessage(), 0, $e);
        }
    }

    public function getPaymentStatus(string $paymentIntentId): string
    {
        try {
            $paymentIntent = $this->stripe->paymentIntents->retrieve($paymentIntentId);
            return $paymentIntent->status;
        } catch (ApiErrorException $e) {
            throw new PaymentProviderException('Failed to get payment status: ' . $e->getMessage(), 0, $e);
        }
    }
}
