<?php

namespace Tests\Unit\Application\Query\GetProduct;

use App\Application\Query\GetProduct\GetProductHandler;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Uid\Uuid;

class GetProductHandlerTest extends TestCase
{
    private ProductRepositoryInterface $productRepository;
    private GetProductHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new GetProductHandler($this->productRepository);
    }

    public function testGetExistingProduct(): void
    {
        $productId = Uuid::v4();
        $expectedProduct = new Product('Test Product', 1000);

        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with($productId)
            ->willReturn($expectedProduct);

        $query = new GetProductQuery($productId);
        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedProduct, $result);
    }

    public function testGetNonExistingProduct(): void
    {
        $productId = Uuid::v4();

        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with($productId)
            ->willReturn(null);

        $query = new GetProductQuery($productId);
        $result = $this->handler->__invoke($query);

        $this->assertNull($result);
    }
}
