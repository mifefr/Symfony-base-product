<?php

namespace App\Tests\Unit\Application\Query\GetProduct;

use App\Application\Query\GetProduct\GetProductHandler;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ProductView;
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
        $product = new Product($productId, 'Test Product', new Money(1000), 'A description');

        $this->productRepository
            ->expects($this->once())
            ->method('findById')
            ->with($productId)
            ->willReturn($product);

        $result = $this->handler->__invoke(new GetProductQuery($productId));

        $this->assertInstanceOf(ProductView::class, $result);
        $this->assertSame((string) $productId, $result->id);
        $this->assertSame('Test Product', $result->name);
        $this->assertSame(10.0, $result->price);
        $this->assertSame('A description', $result->description);
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
