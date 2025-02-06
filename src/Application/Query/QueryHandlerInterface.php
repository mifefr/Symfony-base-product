<?php

declare(strict_types=1);

namespace App\Application\Query;

interface QueryHandlerInterface
{
    public function __invoke(QueryInterface $query): mixed;
}
