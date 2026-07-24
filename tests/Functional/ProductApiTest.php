<?php

declare(strict_types=1);

namespace App\Tests\Functional;

use App\Domain\Model\Product;
use App\Domain\ValueObject\Money;
use App\Domain\ValueObject\ProductId;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class ProductApiTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->client->disableReboot();

        $this->entityManager = static::getContainer()->get(EntityManagerInterface::class);
        $schemaTool = new SchemaTool($this->entityManager);
        $metadata = $this->entityManager->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testCreateProduct(): void
    {
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => 'Functional Test Product',
            'price' => 19.99,
            'description' => 'Created by functional test',
        ]));

        self::assertResponseStatusCodeSame(201);
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $products = $this->entityManager->getRepository(Product::class)->findAll();
        self::assertCount(1, $products);
        self::assertSame('Functional Test Product', $products[0]->getName());
        self::assertSame(1999, $products[0]->getPrice()->getAmountInCents());
        self::assertSame((string) $products[0]->getId(), $responseData['id']);
    }

    public function testCreateProductWithoutNameReturns422(): void
    {
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'price' => 19.99,
        ]));

        self::assertResponseStatusCodeSame(422);
    }

    public function testCreateProductWithEmptyNameReturns422(): void
    {
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => '',
            'price' => 19.99,
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, count($this->entityManager->getRepository(Product::class)->findAll()));
    }

    public function testCreateProductWithNegativePriceReturns422(): void
    {
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => 'Bad Product',
            'price' => -5.0,
        ]));

        self::assertResponseStatusCodeSame(422);
        self::assertSame(0, count($this->entityManager->getRepository(Product::class)->findAll()));
    }

    public function testListProducts(): void
    {
        $this->persistProduct(new Product(ProductId::generate(), 'Product A', new Money(1000), 'First'));
        $this->persistProduct(new Product(ProductId::generate(), 'Product B', new Money(2050), 'Second'));

        $this->client->request('GET', '/api/products');

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertCount(2, $data);
        self::assertSame(['Product A', 'Product B'], array_column($data, 'name'));
        self::assertSame(['id', 'name', 'price', 'description'], array_keys($data[0]));
        self::assertIsString($data[0]['id']);
    }

    public function testGetProduct(): void
    {
        $product = new Product(ProductId::generate(), 'Single Product', new Money(4250), 'Details');
        $this->persistProduct($product);

        $this->client->request('GET', '/api/products/' . $product->getId());

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertSame(['id', 'name', 'price', 'description'], array_keys($data));
        self::assertSame('Single Product', $data['name']);
        self::assertSame(42.5, $data['price']);
        self::assertSame((string) $product->getId(), $data['id']);
    }

    public function testGetUnknownProductReturns404(): void
    {
        $this->client->request('GET', '/api/products/00000000-0000-4000-8000-000000000000');

        self::assertResponseStatusCodeSame(404);
    }

    public function testGetProductWithInvalidIdReturns400(): void
    {
        $this->client->request('GET', '/api/products/not-a-uuid');

        self::assertResponseStatusCodeSame(400);
    }

    public function testCreateProductWithWhitespaceNameHitsDomainValidation(): void
    {
        // passes the NotBlank surface check but is rejected by the domain
        $this->client->request('POST', '/api/products', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
            'name' => '   ',
            'price' => 19.99,
        ]));

        self::assertResponseStatusCodeSame(400);
        self::assertSame(0, count($this->entityManager->getRepository(Product::class)->findAll()));
    }

    private function persistProduct(Product $product): void
    {
        $this->entityManager->persist($product);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}
