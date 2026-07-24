<?php

namespace App\Tests\Unit\Application\Query\ListProducts;

use App\Application\Query\ListProducts\ListProductsQueryHandler;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Application\Query\ProductView;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Domain\Repository\ProductRepositoryInterface;
use PHPUnit\Framework\TestCase;

class ListProductsQueryHandlerTest extends TestCase
{
    private ProductRepositoryInterface $productRepository;
    private ListProductsQueryHandler $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new ListProductsQueryHandler($this->productRepository);
    }

    public function testListProducts(): void
    {
        $products = [
            new Product(ProductId::generate(), 'Product 1', new Money(1000)),
            new Product(ProductId::generate(), 'Product 2', new Money(2000)),
        ];

        $this->productRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn($products);

        $query = new ListProductsQuery();
        $result = $this->handler->__invoke($query);

        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(ProductView::class, $result);
        $this->assertSame(['Product 1', 'Product 2'], array_map(fn (ProductView $v) => $v->name, $result));
        $this->assertSame([10.0, 20.0], array_map(fn (ProductView $v) => $v->price, $result));
    }

    public function testListProductsWhenEmpty(): void
    {
        $this->productRepository
            ->expects($this->once())
            ->method('findAll')
            ->willReturn([]);

        $query = new ListProductsQuery();
        $result = $this->handler->__invoke($query);

        $this->assertEmpty($result);
    }
}
