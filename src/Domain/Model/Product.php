<?php

namespace App\Domain\Model;

use Symfony\Component\Uid\Uuid;

class Product
{
    private Uuid $id;
    private string $name;
    private int $priceInCents;
    private ?string $description;

    public function __construct(
        Uuid $id,
        string $name,
        int $priceInCents,
        ?string $description = null
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }
        if ($priceInCents < 0) {
            throw new \InvalidArgumentException('Product price cannot be negative');
        }

        $this->id = $id;
        $this->name = $name;
        $this->priceInCents = $priceInCents;
        $this->description = $description;
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPriceInCents(): int
    {
        return $this->priceInCents;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
