<?php

namespace App\Tests\Unit\Application\Command\CreateProduct;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Command\CreateProduct\CreateProductHandler;
use App\Domain\Model\Product;
use App\Domain\Repository\ProductRepositoryInterface;
use Mockery;
use PHPUnit\Framework\TestCase;

class CreateProductHandlerTest extends TestCase
{
    private $productRepository;
    private $handler;

    protected function setUp(): void
    {
        $this->productRepository = Mockery::mock(ProductRepositoryInterface::class);
        $this->handler = new CreateProductHandler($this->productRepository);
    }

    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function testHandleCreatesAndSavesProduct(): void
    {
        $command = new CreateProductCommand(
            name: 'Test Product',
            price: 99.99,
            description: 'Test Description'
        );

        $this->productRepository
            ->shouldReceive('save')
            ->once()
            ->with(Mockery::type(Product::class));

        $this->handler->__invoke($command);
    }

    public function testHandleThrowsExceptionForInvalidCommand(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        
        $invalidCommand = Mockery::mock('App\Application\Command\CommandInterface');
        $this->handler->__invoke($invalidCommand);
    }
}
