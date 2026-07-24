<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Application\Command\CommandInterface;

final class CreateProductCommand implements CommandInterface
{
    public function __construct(
        public readonly string $name,
        public readonly int $priceInCents,
        public readonly string $description
    ) {
    }
}
