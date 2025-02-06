<?php

namespace App\Tests\Integration\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ProductControllerTest extends WebTestCase
{
    public function testCreateProduct(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Product',
                'price' => 99.99,
                'description' => 'Test Description'
            ])
        );

        $this->assertEquals(Response::HTTP_CREATED, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
    }

    public function testGetProduct(): void
    {
        $client = static::createClient();

        // First create a product
        $client->request(
            'POST',
            '/api/products',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'name' => 'Test Product',
                'price' => 99.99,
                'description' => 'Test Description'
            ])
        );

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $productId = $responseData['id'] ?? null;

        // Then try to get it
        $client->request('GET', "/api/products/{$productId}");

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $product = json_decode($client->getResponse()->getContent(), true);
        $this->assertEquals('Test Product', $product['name']);
        $this->assertEquals(99.99, $product['price']);
    }
}
