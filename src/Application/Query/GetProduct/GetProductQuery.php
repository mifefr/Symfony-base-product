<?php

declare(strict_types=1);

namespace App\Application\Query\GetProduct;

use App\Application\Query\QueryInterface;
use Symfony\Component\Uid\Uuid;

final class GetProductQuery implements QueryInterface
{
    public function __construct(
        public readonly Uuid $id
    ) {
    }
}
