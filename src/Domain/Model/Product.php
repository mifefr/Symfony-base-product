<?php

namespace App\Domain\Model;

use Symfony\Component\Uid\Uuid;

class Product
{
    private Uuid $id;
    private string $name;
    private float $price;
    private ?string $description;

    public function __construct(
        string $name, 
        float $price, 
        ?string $description = null
    ) {
        $this->id = Uuid::v4();
        $this->name = $name;
        $this->price = $price;
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

    public function getPrice(): float
    {
        return $this->price;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }
}
