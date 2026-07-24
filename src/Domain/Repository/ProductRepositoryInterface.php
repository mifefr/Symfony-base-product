<?php

namespace App\Domain\Repository;

use App\Domain\Model\Product;
use App\Domain\ValueObject\ProductId;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    public function findById(ProductId $id): ?Product;
    public function findAll(): array;
    public function delete(Product $product): void;
}
