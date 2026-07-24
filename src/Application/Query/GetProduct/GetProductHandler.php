<?php

declare(strict_types=1);

namespace App\Application\Query\GetProduct;

use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(GetProductQuery $query): ?Product
    {
        return $this->productRepository->findById($query->id);
    }
}
