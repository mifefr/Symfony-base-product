<?php

declare(strict_types=1);

namespace App\Domain\Port;

use App\Domain\Model\Payment;
use App\Domain\ValueObject\Money;

interface PaymentServiceInterface
{
    public function createPaymentIntent(Money $amount): Payment;
    public function getPaymentStatus(string $paymentIntentId): string;
}
