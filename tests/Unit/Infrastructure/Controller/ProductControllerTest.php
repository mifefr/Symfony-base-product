<?php

namespace App\Tests\Unit\Infrastructure\Controller;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Domain\Model\Product;
use App\Infrastructure\Controller\ProductController;
use App\Tests\Unit\Infrastructure\Controller\AbstractControllerTestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Uid\Uuid;

class ProductControllerTest extends AbstractControllerTestCase
{
    private ProductController $controller;
    private MessageBusInterface $commandBus;
    private MessageBusInterface $queryBus;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->queryBus = $this->createMock(MessageBusInterface::class);
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
                    && $command->priceInCents === 10000
                    && $command->description === $requestData['description'];
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Product created', $responseData['status']);
        $this->assertTrue(Uuid::isValid($responseData['id']));
    }

    public function testGet(): void
    {
        $productId = Uuid::v4();
        $product = new Product(Uuid::v4(), 'Test Product', 10000);

        $envelope = new Envelope(new \stdClass(), [
            new HandledStamp($product, 'handler')
        ]);

        $this->queryBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->callback(function ($query) use ($productId) {
                return $query instanceof GetProductQuery && $query->id->equals($productId);
            }))
            ->willReturn($envelope);

        $response = $this->controller->get($productId->__toString());

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testListProducts(): void
    {
        $products = [
            new Product(Uuid::v4(), 'Product 1', 10000),
            new Product(Uuid::v4(), 'Product 2', 20000)
        ];

        $envelope = new Envelope(new \stdClass(), [
            new HandledStamp($products, 'handler')
        ]);

        $this->queryBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ListProductsQuery::class))
            ->willReturn($envelope);

        $response = $this->controller->listProducts();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
