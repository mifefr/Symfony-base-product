<?php

declare(strict_types=1);

namespace App\Infrastructure\Request;

use Symfony\Component\Validator\Constraints as Assert;

final class CreatePaymentRequest
{
    #[Assert\NotNull(message: 'Amount is required')]
    #[Assert\Positive(message: 'Amount must be greater than 0')]
    public ?float $amount = null;
}
