<?php

declare(strict_types=1);

namespace App\Application\Query;

use App\Domain\Model\Product;

final class ProductView
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $description
    ) {
    }

    public static function fromProduct(Product $product): self
    {
        return new self(
            (string) $product->getId(),
            $product->getName(),
            $product->getPrice()->toDecimal(),
            $product->getDescription()
        );
    }
}
