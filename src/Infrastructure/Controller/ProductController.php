<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Bus\CommandBusInterface;
use App\Application\Bus\QueryBusInterface;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus
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

        try {
            $command = new CreateProductCommand(
                name: $data['name'],
                price: Money::fromDecimal((float) $data['price']),
                description: $data['description'] ?? ''
            );

            $this->commandBus->dispatch($command);
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
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
            $product = $this->queryBus->ask(new GetProductQuery(ProductId::fromString($id)));
        } catch (\InvalidArgumentException $e) {
            return new JsonResponse(['error' => 'Invalid product ID format'], Response::HTTP_BAD_REQUEST);
        }

        if (!$product instanceof Product) {
            return new JsonResponse(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($this->serializeProduct($product));
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        $products = $this->queryBus->ask(new ListProductsQuery());

        if (!is_array($products)) {
            $products = [];
        }

        return $this->json(array_map(
            fn (Product $product) => $this->serializeProduct($product),
            $products
        ));
    }

    private function serializeProduct(Product $product): array
    {
        return [
            'id' => (string) $product->getId(),
            'name' => $product->getName(),
            'price' => $product->getPrice()->toDecimal(),
            'description' => $product->getDescription(),
        ];
    }
}
