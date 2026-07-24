<?php

namespace App\Domain\Model;

use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

class Product
{
    private ProductId $id;
    private string $name;
    private Money $price;
    private ?string $description;

    public function __construct(
        ProductId $id,
        string $name,
        Money $price,
        ?string $description = null
    ) {
        if (trim($name) === '') {
            throw new \InvalidArgumentException('Product name cannot be empty');
        }

        $this->id = $id;
        $this->name = $name;
        $this->price = $price;
        $this->description = $description;
    }

    public function getId(): ProductId
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getPrice(): Money
    {
        return $this->price;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
