<?php

namespace Tests\Integration\Infrastructure\Controller;

use PHPUnit\Framework\TestCase;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\ListProducts\ListProductsQuery;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\Stamp\HandledStamp;

class TestProductController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {}

    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $command = new CreateProductCommand(
            $data['name'],
            (float) $data['price'],
            $data['description']
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(null, 201);
    }

    public function list(): JsonResponse
    {
        $query = new ListProductsQuery();
        $envelope = $this->queryBus->dispatch($query);
        $handledStamp = $envelope->last(HandledStamp::class);
        $result = $handledStamp ? $handledStamp->getResult() : [];

        return new JsonResponse($result);
    }
}

class ProductControllerTest extends TestCase
{
    private $controller;
    private $commandBus;
    private $queryBus;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(MessageBusInterface::class);
        $this->queryBus = $this->createMock(MessageBusInterface::class);
        $this->controller = new TestProductController($this->commandBus, $this->queryBus);
    }

    public function testCreateProduct(): void
    {
        $request = new Request([], [], [], [], [], [], json_encode([
            'name' => 'Test Product',
            'price' => 1000.0,
            'description' => 'Test Description'
        ]));

        $envelope = new Envelope(new \stdClass(), [
            new HandledStamp(new \stdClass(), 'handler.service.id')
        ]);

        $this->commandBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(CreateProductCommand::class))
            ->willReturn($envelope);

        $response = $this->controller->create($request);

        $this->assertEquals(201, $response->getStatusCode());
    }

    public function testListProducts(): void
    {
        $envelope = new Envelope(new \stdClass(), [
            new HandledStamp([], 'handler.service.id')
        ]);

        $this->queryBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(ListProductsQuery::class))
            ->willReturn($envelope);

        $response = $this->controller->list();

        $this->assertEquals(200, $response->getStatusCode());
    }
}
