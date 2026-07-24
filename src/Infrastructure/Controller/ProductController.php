<?php

declare(strict_types=1);

namespace App\Infrastructure\Controller;

use App\Application\Bus\CommandBusInterface;
use App\Application\Bus\QueryBusInterface;
use App\Application\Command\CreateProduct\CreateProductCommand;
use App\Application\Query\GetProduct\GetProductQuery;
use App\Application\Query\ListProducts\ListProductsQuery;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use App\Infrastructure\Request\CreateProductRequest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\MapRequestPayload;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly QueryBusInterface $queryBus
    ) {
    }

    #[Route('/api/products', name: 'create_product', methods: ['POST'])]
    public function create(#[MapRequestPayload] CreateProductRequest $request): JsonResponse
    {
        $command = new CreateProductCommand(
            name: $request->name,
            price: Money::fromDecimal($request->price),
            description: $request->description ?? ''
        );

        $this->commandBus->dispatch($command);

        return new JsonResponse([
            'id' => (string) $command->id,
            'status' => 'Product created',
        ], Response::HTTP_CREATED);
    }

    #[Route('/api/products/{id}', name: 'get_product', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        return $this->json(
            $this->queryBus->ask(new GetProductQuery(ProductId::fromString($id)))
        );
    }

    #[Route('/api/products', name: 'list_products', methods: ['GET'])]
    public function listProducts(): JsonResponse
    {
        $views = $this->queryBus->ask(new ListProductsQuery());

        return $this->json(is_array($views) ? $views : []);
    }
}
