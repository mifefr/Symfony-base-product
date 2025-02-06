<?php

declare(strict_types=1);

namespace App\Application\Query\ListProducts;

use App\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListProductsQueryHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(ListProductsQuery $query): array
    {
        return $this->productRepository->findAll();
    }
}
