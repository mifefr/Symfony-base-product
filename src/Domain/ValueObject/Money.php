<?php

declare(strict_types=1);

namespace App\Domain\ValueObject;

final class Money
{
    public function __construct(
        private readonly int $amountInCents,
        private readonly string $currency = 'EUR'
    ) {
        if ($amountInCents < 0) {
            throw new \InvalidArgumentException('Amount cannot be negative');
        }
        if (!preg_match('/^[A-Z]{3}$/', $currency)) {
            throw new \InvalidArgumentException('Currency must be a 3-letter uppercase ISO code');
        }
    }

    public static function fromDecimal(float $amount, string $currency = 'EUR'): self
    {
        return new self((int) round($amount * 100), $currency);
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
    }

    public function getCurrency(): string
    {
        return $this->currency;
    }

    public function toDecimal(): float
    {
        return $this->amountInCents / 100;
    }

    public function isZero(): bool
    {
        return $this->amountInCents === 0;
    }

    public function equals(self $other): bool
    {
        return $this->amountInCents === $other->amountInCents
            && $this->currency === $other->currency;
    }
}
