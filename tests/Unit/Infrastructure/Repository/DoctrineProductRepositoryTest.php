<?php

namespace Tests\Unit\Infrastructure\Repository;

use App\Domain\Model\Product;
use App\Infrastructure\Repository\DoctrineProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class DoctrineProductRepositoryTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private EntityRepository $repository;
    private DoctrineProductRepository $doctrineRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->repository = $this->createMock(EntityRepository::class);
        $this->doctrineRepository = new DoctrineProductRepository($this->entityManager);
    }

    public function testSave(): void
    {
        $product = new Product('Test Product', 1000);

        $this->entityManager
            ->expects($this->once())
            ->method('persist')
            ->with($product);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->doctrineRepository->save($product);
    }

    public function testFindById(): void
    {
        $productId = Uuid::v4();
        $expectedProduct = new Product('Test Product', 1000);

        $this->repository
            ->expects($this->once())
            ->method('findOneBy')
            ->with(['id' => $productId])
            ->willReturn($expectedProduct);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->repository);

        $result = $this->doctrineRepository->findById($productId);
        $this->assertSame($expectedProduct, $result);
    }

    public function testFindAll(): void
    {
        $expectedProducts = [
            new Product('Product 1', 1000),
            new Product('Product 2', 2000),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($expectedProducts);

        $this->entityManager
            ->expects($this->once())
            ->method('getRepository')
            ->with(Product::class)
            ->willReturn($this->repository);

        $result = $this->doctrineRepository->findAll();
        $this->assertSame($expectedProducts, $result);
    }

    public function testDelete(): void
    {
        $product = new Product('Test Product', 1000);

        $this->entityManager
            ->expects($this->once())
            ->method('remove')
            ->with($product);

        $this->entityManager
            ->expects($this->once())
            ->method('flush');

        $this->doctrineRepository->delete($product);
    }
}
