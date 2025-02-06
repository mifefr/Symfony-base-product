<?php

namespace Tests\Unit\Infrastructure\Controller;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Domain\Model\Product;
use App\Infrastructure\Controller\ProductController;
use Tests\Unit\Infrastructure\Controller\AbstractControllerTest;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Uid\Uuid;

class ProductControllerTest extends AbstractControllerTest
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
                    && $command->price === $requestData['price']
                    && $command->description === $requestData['description'];
            }))
            ->willReturn(new Envelope(new \stdClass()));

        $response = $this->controller->create($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals(['status' => 'Product created'], json_decode($response->getContent(), true));
    }

    public function testGet(): void
    {
        $productId = Uuid::v4();
        $product = new Product('Test Product', 100.0);

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

    public function testList(): void
    {
        $products = [
            new Product('Product 1', 100.0),
            new Product('Product 2', 200.0)
        ];

        $envelope = new Envelope(new \stdClass(), [
            new HandledStamp($products, 'handler')
        ]);

        $this->queryBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ListProductsQuery::class))
            ->willReturn($envelope);

        $response = $this->controller->list();

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
