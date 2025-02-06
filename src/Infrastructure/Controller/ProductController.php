<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Messenger\Handler\HandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Messenger\Stamp\HandledStamp;
use Symfony\Component\Routing\Annotation\Route;

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

        $command = new CreateProductCommand(
            name: $data['name'],
            price: (float) $data['price'],
            description: $data['description']
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse(['status' => 'Product created'], Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        $query = new GetProductQuery($id);
        $product = $this->queryBus->dispatch($query)->getHandlerResult();

        return $this->json($product);
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function list(): JsonResponse
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
                'price' => $product->getPrice(),
                'description' => $product->getDescription(),
            ];
        }, $products);

        return $this->json($productsArray);
    }
}
