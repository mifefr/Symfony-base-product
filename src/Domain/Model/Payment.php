<?php

declare(strict_types=1);

namespace App\Domain\Model;

class Payment
{
    private string $id;
    private float $amount;
    private string $currency;
    private string $status;
    private ?string $clientSecret;

    public function __construct(
        string $id,
        float $amount,
        string $currency,
        string $status,
        ?string $clientSecret = null
    ) {
        $this->id = $id;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->status = $status;
        $this->clientSecret = $clientSecret;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function getCurrency(): string
    {
        return $this->currency;
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
