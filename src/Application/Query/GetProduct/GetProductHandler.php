<?php

declare(strict_types=1);

namespace App\Application\Query\GetProduct;

use App\Application\Query\QueryHandlerInterface;
use App\Application\Query\QueryInterface;
use App\Domain\Repository\ProductRepositoryInterface;

final class GetProductHandler implements QueryHandlerInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(QueryInterface $query): mixed
    {
        if (!$query instanceof GetProductQuery) {
            throw new \InvalidArgumentException('Invalid query type');
        }

        return $this->productRepository->findById($query->id);
    }
}
