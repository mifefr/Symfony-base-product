<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
class CreateProductCommandHandler
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(CreateProductCommand $command): void
    {
        $product = new Product(
            $command->name,
            $command->price,
            $command->description
        );

        $this->productRepository->save($product);
    }
}
