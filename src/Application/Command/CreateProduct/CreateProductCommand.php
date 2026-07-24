<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Application\Command\CommandInterface;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

final class CreateProductCommand implements CommandInterface
{
    public readonly ProductId $id;

    public function __construct(
        public readonly string $name,
        public readonly Money $price,
        public readonly string $description
    ) {
        $this->id = ProductId::generate();
    }
}
