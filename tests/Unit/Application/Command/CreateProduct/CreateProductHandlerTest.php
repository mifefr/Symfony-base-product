<?php

namespace Tests\Unit\Application\Command\CreateProduct;

use PHPUnit\Framework\TestCase;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Command\CreateProduct\CreateProductCommandHandler;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;

class CreateProductHandlerTest extends TestCase
{
    private $productRepository;
    private $handler;

    protected function setUp(): void
    {
        $this->productRepository = $this->createMock(ProductRepositoryInterface::class);
        $this->handler = new CreateProductCommandHandler($this->productRepository);
    }

    public function testHandle(): void
    {
        $command = new CreateProductCommand('Test Product', 1000.0, 'Test Description');

        $this->productRepository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(function ($product) {
                return $product instanceof Product
                    && $product->getName() === 'Test Product'
                    && $product->getPrice() === 1000.0
                    && $product->getDescription() === 'Test Description';
            }));

        $this->handler->__invoke($command);
    }
}
