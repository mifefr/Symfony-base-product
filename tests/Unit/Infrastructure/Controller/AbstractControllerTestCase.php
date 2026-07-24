<?php

namespace App\Tests\Unit\Infrastructure\Controller;

use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Container;

abstract class AbstractControllerTestCase extends TestCase
{
    protected Container $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = new Container();
        $this->container->set('parameter_bag', null);
    }

    protected function setContainer($controller): void
    {
        $controller->setContainer($this->container);
    }
}
