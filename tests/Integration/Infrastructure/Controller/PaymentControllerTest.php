<?php

namespace App\Tests\Integration\Infrastructure\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentControllerTest extends WebTestCase
{
    public function testCreatePayment(): void
    {
        $client = static::createClient();

        $client->request(
            'POST',
            '/api/payment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount' => 100.00
            ])
        );

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('clientSecret', $responseData);
        $this->assertArrayHasKey('paymentId', $responseData);
    }

    public function testGetPaymentStatus(): void
    {
        $client = static::createClient();

        // First create a payment
        $client->request(
            'POST',
            '/api/payment/create',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'amount' => 100.00
            ])
        );

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $paymentId = $responseData['paymentId'];

        // Then check its status
        $client->request('GET', "/api/payment/{$paymentId}/status");

        $this->assertEquals(Response::HTTP_OK, $client->getResponse()->getStatusCode());
        $this->assertJson($client->getResponse()->getContent());
        
        $statusData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('status', $statusData);
    }
}
