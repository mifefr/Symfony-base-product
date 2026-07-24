<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function testCreateMoney(): void
    {
        $money = new Money(1999);

        $this->assertSame(1999, $money->getAmountInCents());
        $this->assertSame('EUR', $money->getCurrency());
        $this->assertSame(19.99, $money->toDecimal());
        $this->assertFalse($money->isZero());
    }

    public function testFromDecimalRoundsCorrectly(): void
    {
        // 4.10 * 100 is 409.999... as a float; rounding must yield 410
        $this->assertSame(410, Money::fromDecimal(4.10)->getAmountInCents());
        $this->assertSame(1999, Money::fromDecimal(19.99)->getAmountInCents());
    }

    public function testZero(): void
    {
        $this->assertTrue((new Money(0))->isZero());
    }

    public function testNegativeAmountThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        new Money(-1);
    }

    public function testInvalidCurrencyThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        new Money(100, 'euros');
    }

    public function testEquals(): void
    {
        $this->assertTrue((new Money(100, 'EUR'))->equals(new Money(100, 'EUR')));
        $this->assertFalse((new Money(100, 'EUR'))->equals(new Money(100, 'USD')));
        $this->assertFalse((new Money(100))->equals(new Money(200)));
    }
}
