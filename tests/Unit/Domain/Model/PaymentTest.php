<?php

namespace App\Tests\Unit\Domain\Model;

use App\Domain\Model\Payment;
use PHPUnit\Framework\TestCase;

class PaymentTest extends TestCase
{
    public function testCreatePayment(): void
    {
        $payment = new Payment(
            id: 'pi_123',
            amount: 100.00,
            currency: 'eur',
            status: 'pending',
            clientSecret: 'secret_123'
        );

        $this->assertEquals('pi_123', $payment->getId());
        $this->assertEquals(100.00, $payment->getAmount());
        $this->assertEquals('eur', $payment->getCurrency());
        $this->assertEquals('pending', $payment->getStatus());
        $this->assertEquals('secret_123', $payment->getClientSecret());
    }
}
