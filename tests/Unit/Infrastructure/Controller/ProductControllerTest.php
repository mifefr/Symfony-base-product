<?php

namespace App\Tests\Unit\Infrastructure\Controller;

use App\Application\Bus\CommandBusInterface;
use App\Application\Bus\QueryBusInterface;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Controller\ProductController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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
        $requestData = [
            'name' => 'Test Product',
            'price' => 100.0,
            'description' => 'Test Description'
        ];

        $request = new Request([], [], [], [], [], [], json_encode($requestData));

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function (CreateProductCommand $command) use ($requestData) {
                return $command->name === $requestData['name']
                    && $command->price->getAmountInCents() === 10000
                    && $command->description === $requestData['description'];
            }));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Product created', $responseData['status']);
        $this->assertTrue(Uuid::isValid($responseData['id']));
    }

    public function testCreateWithMissingNameReturns400(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode(['price' => 10.0]));

        $this->commandBus->expects($this->never())->method('dispatch');

        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testCreateWithDomainValidationErrorReturns400(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => '   ',
            'price' => 10.0,
        ]));

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->willThrowException(new \InvalidArgumentException('Product name cannot be empty'));

        $response = $this->controller->create($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Product name cannot be empty', json_decode($response->getContent(), true)['error']);
    }

    public function testGet(): void
    {
        $productId = ProductId::generate();
        $product = new Product($productId, 'Test Product', new Money(10000));

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with($this->callback(function (GetProductQuery $query) use ($productId) {
                return $query->id->equals($productId);
            }))
            ->willReturn($product);

        $response = $this->controller->get((string) $productId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Test Product', json_decode($response->getContent(), true)['name']);
    }

    public function testGetNotFoundReturns404(): void
    {
        $this->queryBus->method('ask')->willReturn(null);

        $response = $this->controller->get((string) ProductId::generate());

        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testGetWithInvalidIdReturns400(): void
    {
        $this->queryBus->expects($this->never())->method('ask');

        $response = $this->controller->get('not-a-uuid');

        $this->assertEquals(400, $response->getStatusCode());
    }

    public function testListProducts(): void
    {
        $products = [
            new Product(ProductId::generate(), 'Product 1', new Money(10000)),
            new Product(ProductId::generate(), 'Product 2', new Money(20000))
        ];

        $this->queryBus
            ->expects($this->once())
            ->method('ask')
            ->with($this->isInstanceOf(ListProductsQuery::class))
            ->willReturn($products);

        $response = $this->controller->listProducts();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertCount(2, json_decode($response->getContent(), true));
    }
}
