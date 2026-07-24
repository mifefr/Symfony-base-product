<?php

declare(strict_types=1);

namespace App\Application\Query\ListProducts;

use App\Application\Query\ProductView;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final class ListProductsQueryHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    /**
     * @return ProductView[]
     */
    public function __invoke(ListProductsQuery $query): array
    {
        return array_map(
            fn (Product $product) => ProductView::fromProduct($product),
            $this->productRepository->findAll()
        );
    }
}
