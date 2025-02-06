<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Payment;

interface PaymentServiceInterface
{
    public function createPaymentIntent(float $amount, string $currency = 'eur'): Payment;
    public function confirmPayment(string $paymentIntentId): bool;
    public function getPaymentStatus(string $paymentIntentId): string;
}
