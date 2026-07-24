<?php

namespace Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Product;
use Symfony\Component\Uid\Uuid;

class ProductTest extends TestCase
{
    public function testCreateProduct(): void
    {
        $product = new Product(Uuid::v4(), 'Test Product', 100000);
        
        $this->assertInstanceOf(Uuid::class, $product->getId());
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals(100000, $product->getPriceInCents());
        $this->assertNull($product->getDescription());
    }

    public function testCreateProductWithDescription(): void
    {
        $product = new Product(Uuid::v4(), 'Test Product', 100000, 'Test Description');
        
        $this->assertEquals('Test Description', $product->getDescription());
    }
}
