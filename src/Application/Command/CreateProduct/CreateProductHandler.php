<?php

declare(strict_types=1);

namespace App\Application\Command\CreateProduct;

use App\Application\Command\CommandHandlerInterface;
use App\Application\Command\CommandInterface;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;

final class CreateProductHandler implements CommandHandlerInterface
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository
    ) {
    }

    public function __invoke(CommandInterface $command): void
    {
        if (!$command instanceof CreateProductCommand) {
            throw new \InvalidArgumentException('Invalid command type');
        }

        $product = new Product(
            name: $command->name,
            price: $command->price,
            description: $command->description
        );

        $this->productRepository->save($product);
    }
}
