<?php

declare(strict_types=1);

namespace App\Domain\Model;

class Payment
{
    private string $id;
    private int $amountInCents;
    private string $currency;
    private string $status;
    private ?string $clientSecret;

    public function __construct(
        string $id,
        int $amountInCents,
        string $currency,
        string $status,
        ?string $clientSecret = null
    ) {
        $this->id = $id;
        $this->amountInCents = $amountInCents;
        $this->currency = $currency;
        $this->status = $status;
        $this->clientSecret = $clientSecret;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getAmountInCents(): int
    {
        return $this->amountInCents;
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
