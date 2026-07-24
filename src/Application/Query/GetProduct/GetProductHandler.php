<?php

declare(strict_types=1);

namespace App\Application\Query\GetProduct;

use App\Application\Query\ProductView;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class GetProductHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(GetProductQuery $query): ProductView
    {
        $product = $this->productRepository->findById($query->id);

        if ($product === null) {
            throw ProductNotFoundException::withId($query->id);
        }

        return ProductView::fromProduct($product);
    }
}
