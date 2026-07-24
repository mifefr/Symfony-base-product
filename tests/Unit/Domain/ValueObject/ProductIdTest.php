<?php

namespace App\Tests\Unit\Domain\ValueObject;

use App\Domain\ValueObject\ProductId;
use PHPUnit\Framework\TestCase;

class ProductIdTest extends TestCase
{
    public function testGenerateProducesValidUuid(): void
    {
        $id = ProductId::generate();

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-4[0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/',
            (string) $id
        );
    }

    public function testGenerateIsUnique(): void
    {
        $this->assertFalse(ProductId::generate()->equals(ProductId::generate()));
    }

    public function testFromStringRoundTrip(): void
    {
        $value = '0f14d0ab-9605-4a62-a9e4-5ed26688389b';

        $this->assertSame($value, (string) ProductId::fromString($value));
    }

    public function testFromStringNormalizesCase(): void
    {
        $this->assertSame(
            '0f14d0ab-9605-4a62-a9e4-5ed26688389b',
            (string) ProductId::fromString('0F14D0AB-9605-4A62-A9E4-5ED26688389B')
        );
    }

    public function testFromStringRejectsInvalidFormat(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        ProductId::fromString('not-a-uuid');
    }

    public function testEquals(): void
    {
        $value = '0f14d0ab-9605-4a62-a9e4-5ed26688389b';

        $this->assertTrue(ProductId::fromString($value)->equals(ProductId::fromString($value)));
    }
}
