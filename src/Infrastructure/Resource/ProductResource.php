<?php

namespace App\Infrastructure\Resource;

use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use App\Application\UseCase\CreateProductUseCase;
use App\Application\DTO\CreateProductDTO;
use App\Domain\Model\Product;
use Symfony\Component\Serializer\Annotation\Groups;

#[ApiResource(
    operations: [
        new Get(normalizationContext: ['groups' => ['read']]),
        new GetCollection(),
        new Post(
            normalizationContext: ['groups' => ['read']],
            denormalizationContext: ['groups' => ['write']]
        )
    ],
    normalizationContext: ['groups' => ['read']],
    denormalizationContext: ['groups' => ['write']]
)]
class ProductResource
{
    #[Groups(['read'])]
    public string $id;

    #[Groups(['read', 'write'])]
    public string $name;

    #[Groups(['read', 'write'])]
    public float $price;

    #[Groups(['read', 'write'])]
    public ?string $description = null;

    private CreateProductUseCase $createProductUseCase;

    public function __construct(CreateProductUseCase $createProductUseCase)
    {
        $this->createProductUseCase = $createProductUseCase;
    }

    public function create(ProductResource $data): Product
    {
        $dto = new CreateProductDTO(
            $data->name, 
            $data->price, 
            $data->description
        );

        return $this->createProductUseCase->execute($dto);
    }
}
