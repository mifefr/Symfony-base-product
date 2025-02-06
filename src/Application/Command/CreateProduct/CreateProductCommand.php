<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Application\Command\CommandInterface;

final class CreateProductCommand implements CommandInterface
{
    public function __construct(
        public readonly string $name,
        public readonly float $price,
        public readonly string $description
    ) {
    }
}
