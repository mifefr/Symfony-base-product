<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Application\Command\CommandInterface;
use Symfony\Component\Uid\Uuid;

final class CreateProductCommand implements CommandInterface
{
    public readonly Uuid $id;

    public function __construct(
        public readonly string $name,
        public readonly int $priceInCents,
        public readonly string $description
    ) {
        $this->id = Uuid::v4();
    }
}
