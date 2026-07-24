<?php

namespace App\Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;

class ProductTest extends TestCase
{
    public function testCreateProduct(): void
    {
        $id = ProductId::generate();
        $product = new Product($id, 'Test Product', new Money(100000));

        $this->assertTrue($id->equals($product->getId()));
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals(100000, $product->getPrice()->getAmountInCents());
        $this->assertNull($product->getDescription());
    }

    public function testCreateProductWithDescription(): void
    {
        $product = new Product(ProductId::generate(), 'Test Product', new Money(100000), 'Test Description');

        $this->assertEquals('Test Description', $product->getDescription());
    }

    public function testCreateProductWithEmptyNameThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        new Product(ProductId::generate(), '   ', new Money(100000));
    }
}
