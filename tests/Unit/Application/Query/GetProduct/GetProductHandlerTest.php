<?php

namespace App\Tests\Unit\Application\Query\GetProduct;

use App\Application\Query\GetProduct\GetProductHandler;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

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
        $productId = ProductId::generate();
        $expectedProduct = new Product(ProductId::generate(), 'Test Product', new Money(1000));

        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with($productId)
            ->willReturn($expectedProduct);

        $query = new GetProductQuery($productId);
        $result = $this->handler->__invoke($query);

        $this->assertSame($expectedProduct, $result);
    }

    public function testGetNonExistingProductThrows(): void
    {
        $productId = ProductId::generate();

        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with($productId)
            ->willReturn(null);

        $this->expectException(ProductNotFoundException::class);

        $this->handler->__invoke(new GetProductQuery($productId));
    }
}
