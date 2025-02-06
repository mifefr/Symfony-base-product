<?php

namespace Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Payment;

class PaymentTest extends TestCase
{
    public function testCreatePayment(): void
    {
        $payment = new Payment(
            'pay_123',
            1000.0,
            'EUR',
            'pending'
        );
        
        $this->assertEquals('pay_123', $payment->getId());
        $this->assertEquals(1000.0, $payment->getAmount());
        $this->assertEquals('EUR', $payment->getCurrency());
        $this->assertEquals('pending', $payment->getStatus());
        $this->assertNull($payment->getClientSecret());
    }

    public function testCreatePaymentWithClientSecret(): void
    {
        $payment = new Payment(
            'pay_123',
            1000.0,
            'EUR',
            'pending',
            'secret_123'
        );
        
        $this->assertEquals('secret_123', $payment->getClientSecret());
    }
}
