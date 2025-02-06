<?php

declare(strict_types=1);

namespace App\Application\Query\GetProduct;

use App\Application\Query\QueryInterface;

final class GetProductQuery implements QueryInterface
{
    public function __construct(
        public readonly string $id
    ) {
    }
}
