<?php

declare(strict_types=1);

namespace App\Application\Bus;

use App\Application\Command\CommandInterface;

interface CommandBusInterface
{
    public function dispatch(CommandInterface $command): void;
}
