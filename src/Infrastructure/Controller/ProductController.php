<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Uid\Uuid;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly MessageBusInterface $commandBus,
        private readonly MessageBusInterface $queryBus
    ) {
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'])) {
            return new JsonResponse(['error' => 'Name is required'], Response::HTTP_BAD_REQUEST);
        }

        if (!isset($data['price'])) {
            return new JsonResponse(['error' => 'Price is required'], Response::HTTP_BAD_REQUEST);
        }

        $command = new CreateProductCommand(
            name: $data['name'],
            priceInCents: (int) round((float) $data['price'] * 100),
            description: $data['description'] ?? ''
        );

        try {
            $this->commandBus->dispatch($command);
        } catch (HandlerFailedException $e) {
            $previous = $e->getPrevious();
            if ($previous instanceof \InvalidArgumentException) {
                return new JsonResponse(['error' => $previous->getMessage()], Response::HTTP_BAD_REQUEST);
            }

            throw $e;
        }

        return new JsonResponse([
            'id' => (string) $command->id,
            'status' => 'Product created',
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $uuid = Uuid::fromString($id);
            $query = new GetProductQuery($uuid);
            $envelope = $this->queryBus->dispatch($query);
            $handledStamp = $envelope->last(HandledStamp::class);
            $product = $handledStamp ? $handledStamp->getResult() : null;

            if (!$product) {
                return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
            }

            return $this->json([
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPriceInCents() / 100,
                'description' => $product->getDescription(),
            ]);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid product ID format'], Response::HTTP_BAD_REQUEST);
        }
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        $query = new ListProductsQuery();
        $envelope = $this->queryBus->dispatch($query);
        $products = $envelope->last(HandledStamp::class)?->getResult();

        if (!is_array($products)) {
            $products = [];
        }

        $productsArray = array_map(function ($product) {
            return [
                'id' => $product->getId(),
                'name' => $product->getName(),
                'price' => $product->getPriceInCents() / 100,
                'description' => $product->getDescription(),
            ];
        }, $products);

        return $this->json($productsArray);
    }
}
