<?php

namespace App\Tests\Unit\Infrastructure\Controller;

use App\Application\Bus\CommandBusInterface;
use App\Application\Bus\QueryBusInterface;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Application\Query\ProductView;
use App\Domain\Exception\ProductNotFoundException;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Controller\ProductController;
use App\Infrastructure\Request\CreateProductRequest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Uid\Uuid;

class ProductControllerTest extends AbstractControllerTestCase
{
    private ProductController $controller;
    private CommandBusInterface $commandBus;
    private QueryBusInterface $queryBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->queryBus = $this->createMock(QueryBusInterface::class);
        $this->controller = new ProductController($this->commandBus, $this->queryBus);
        $this->setContainer($this->controller);
    }

    public function testCreate(): void
    {
        $request = new CreateProductRequest();
        $request->name = 'Test Product';
        $request->price = 100.0;
        $request->description = 'Test Description';

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CreateProductCommand $command) {
                return $command->name === 'Test Product'
                    && $command->price->getAmountInCents() === 10000
                    && $command->description === 'Test Description';
            }));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Product created', $responseData['status']);
        $this->assertTrue(Uuid::isValid($responseData['id']));
    }

    public function testCreateWithDomainValidationErrorLetsExceptionPropagate(): void
    {
        $request = new CreateProductRequest();
        $request->name = '   ';
        $request->price = 10.0;

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new \InvalidArgumentException('Product name cannot be empty'));

        $this->expectException(\InvalidArgumentException::class);

        $this->controller->create($request);
    }

    public function testGet(): void
    {
        $productId = ProductId::generate();
        $view = ProductView::fromProduct(new Product($productId, 'Test Product', new Money(10000)));

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with($this->callback(function (GetProductQuery $query) use ($productId) {
                return $query->id->equals($productId);
            }))
            ->willReturn($view);

        $response = $this->controller->get((string) $productId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test Product', json_decode($response->getContent(), true)['name']);
    }

    public function testGetNotFoundLetsDomainExceptionPropagate(): void
    {
        $productId = ProductId::generate();
        $this->queryBus->method('ask')->willThrowException(ProductNotFoundException::withId($productId));

        $this->expectException(ProductNotFoundException::class);

        $this->controller->get((string) $productId);
    }

    public function testGetWithInvalidIdThrows(): void
    {
        $this->queryBus->expects($this->never())->method('ask');

        $this->expectException(\InvalidArgumentException::class);

        $this->controller->get('not-a-uuid');
    }

    public function testListProducts(): void
    {
        $views = [
            ProductView::fromProduct(new Product(ProductId::generate(), 'Product 1', new Money(10000))),
            ProductView::fromProduct(new Product(ProductId::generate(), 'Product 2', new Money(20000)))
        ];

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with($this->isInstanceOf(ListProductsQuery::class))
            ->willReturn($views);

        $response = $this->controller->listProducts();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, json_decode($response->getContent(), true));
    }
}
