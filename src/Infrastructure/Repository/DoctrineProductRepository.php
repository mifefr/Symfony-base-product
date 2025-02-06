<?php

namespace App\Infrastructure\Repository;

use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Uid\Uuid;

class DoctrineProductRepository implements ProductRepositoryInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function save(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
    }

    public function findById(Uuid $id): ?Product
    {
        return $this->entityManager->getRepository(Product::class)->findOneBy(['id' => $id]);
    }

    public function findAll(): array
    {
        return $this->entityManager->getRepository(Product::class)->findAll();
    }

    public function delete(Product $product): void
    {
        $this->entityManager->remove($product);
        $this->entityManager->flush();
    }
}
