<?php

namespace Tests\Unit\Domain\Model;

use PHPUnit\Framework\TestCase;
use App\Domain\Model\Product;
use Symfony\Component\Uid\Uuid;

class ProductTest extends TestCase
{
    public function testCreateProduct(): void
    {
        $product = new Product('Test Product', 1000.0);
        
        $this->assertInstanceOf(Uuid::class, $product->getId());
        $this->assertEquals('Test Product', $product->getName());
        $this->assertEquals(1000.0, $product->getPrice());
        $this->assertNull($product->getDescription());
    }

    public function testCreateProductWithDescription(): void
    {
        $product = new Product('Test Product', 1000.0, 'Test Description');
        
        $this->assertEquals('Test Description', $product->getDescription());
    }
}
