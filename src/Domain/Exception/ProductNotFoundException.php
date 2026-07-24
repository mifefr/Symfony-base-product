<?php

declare(strict_types=1);

namespace App\Domain\Exception;

use App\Domain\ValueObject\ProductId;

final class ProductNotFoundException extends \DomainException
{
    public static function withId(ProductId $id): self
    {
        return new self(sprintf('Product "%s" not found', $id));
    }
}
