<?php

namespace App\Domain\Repository;

use App\Domain\Model\Product;
use Symfony\Component\Uid\Uuid;

interface ProductRepositoryInterface
{
    public function save(Product $product): void;
    public function findById(Uuid $id): ?Product;
    public function findAll(): array;
    public function delete(Product $product): void;
}
