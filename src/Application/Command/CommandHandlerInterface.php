<?php

declare(strict_types=1);

namespace App\Application\Command;

interface CommandHandlerInterface
{
    public function __invoke(CommandInterface $command): void;
}
