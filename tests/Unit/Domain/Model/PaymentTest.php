<?php

namespace App\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Payment;
use App\Domain\ValueObject\Money;

class PaymentTest extends TestCase
{
    public function testCreatePayment(): void
    {
        $payment = new Payment(
            'pay_123',
            new Money(100000),
            'pending'
        );

        $this->assertEquals('pay_123', $payment->getId());
        $this->assertEquals(100000, $payment->getAmount()->getAmountInCents());
        $this->assertEquals('EUR', $payment->getAmount()->getCurrency());
        $this->assertEquals('pending', $payment->getStatus());
        $this->assertNull($payment->getClientSecret());
    }

    public function testCreatePaymentWithClientSecret(): void
    {
        $payment = new Payment(
            'pay_123',
            new Money(100000),
            'pending',
            'secret_123'
        );

        $this->assertEquals('secret_123', $payment->getClientSecret());
    }
}
