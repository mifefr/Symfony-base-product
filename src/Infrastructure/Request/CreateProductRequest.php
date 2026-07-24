<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreateProductRequest
{
    #[Assert\NotBlank(message: 'Name is required')]
    public ?string $name = null;

    #[Assert\NotNull(message: 'Price is required')]
    #[Assert\PositiveOrZero(message: 'Price cannot be negative')]
    public ?float $price = null;

    public ?string $description = null;
}
