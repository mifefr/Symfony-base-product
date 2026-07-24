<?php

declare(strict_types=1);

namespace App\Domain\Model;

use App\Domain\ValueObject\Money;

class Payment
{
    private string $id;
    private Money $amount;
    private string $status;
    private ?string $clientSecret;

    public function __construct(
        string $id,
        Money $amount,
        string $status,
        ?string $clientSecret = null
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->status = $status;
        $this->clientSecret = $clientSecret;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): Money
    {
        return $this->amount;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getClientSecret(): ?string
    {
        return $this->clientSecret;
    }
}
