<?php

declare(strict_types=1);

namespace App\Application\Bus;

use App\Application\Query\QueryInterface;

interface QueryBusInterface
{
    public function ask(QueryInterface $query): mixed;
}
