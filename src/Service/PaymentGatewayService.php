<?php

namespace App\Service;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Psr\Log\LoggerInterface;

class PaymentGatewayService
{
    private HttpClientInterface $client;
    private LoggerInterface $logger;

    public function __construct(HttpClientInterface $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }

    public function processPayment(string $gateway, array $paymentData): array
    {
        if ($gateway === 'shift4') {
            return $this->processShift4Payment($paymentData);
        } elseif ($gateway === 'aci') {
            return $this->processAciPayment($paymentData);
        } else {
            throw new \InvalidArgumentException('Unsupported payment gateway');
        }
    }

    private function processAciPayment(array $paymentData): array
    {
        $this->logger->info('Processing ACI payment', ['data' => $paymentData]);

        try {
            $response = $this->client->request('POST', 'https://api.aci.com/charges', [
                'json' => $paymentData,
            ]);

            return $response->toArray();
        } catch (\Exception $e) {
            $this->logger->error('Error processing ACI payment', ['exception' => $e->getMessage()]);
            throw $e;
        }
    }

    private function processShift4Payment(array $paymentData): array
    {
        $this->logger->info('Processing Shift4 payment', ['data' => $paymentData]);

        // $username = $_ENV['SHIFT4_API_USERNAME'];
        // $password = $_ENV['SHIFT4_API_PASSWORD'];
        // <?php
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.shift4.com/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, 'pr_test_tXHm9qV9qV9bjIRHcQr9PLPa:');
        curl_setopt($ch, CURLOPT_POSTFIELDS, "{\n    \"amount\": 500,\n    \"currency\": \"usd\",\n    \"card\": {\n      \"number\": \"4242424242424242\",\n      \"expMonth\": 12,\n      \"expYear\": 2024,\n      \"cvc\": \"123\"\n    },\n    \"description\": \"Example charge\"\n  }");
        
        $response = curl_exec($ch);
        
        curl_close($ch);
        return[];

        // $requestPayload = [
        //     'amount' => $paymentData['amount'],
        //     'currency' => $paymentData['currency'],
        //     'description' => $paymentData['description'],
        //     'card' => [
        //         'first6' => substr($paymentData['card_number'], 0, 6),
        //         'last4' => substr($paymentData['card_number'], -4),
        //         'expMonth' => $paymentData['card_exp_month'],
        //         'expYear' => $paymentData['card_exp_year'],
        //         'cvv' => $paymentData['card_cvv'],
        //     ]
        // ];

        // $this->logger->info('Shift4 request URL', ['url' => 'https://api.shift4.com/charges']);
        // $this->logger->info('Shift4 request payload', ['payload' => $requestPayload]);
        
        // try {
        //     $response = $this->client->request('POST', 'https://api.shift4.com/charges', [
        //         'json' => $requestPayload,
        //         'auth_basic' => [$username, $password ?: ''],
        //     ]);
        
        //     $responseData = $response->toArray();
        //     $this->logger->info('Shift4 response', ['response' => $responseData]);
        
        //     return $responseData;
        // } catch (\Exception $e) {
        //     if ($e instanceof \Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface) {
        //         $response = $e->getResponse();
        //         $responseContent = $response->getContent(false);
        //         $this->logger->error('Error processing Shift4 payment', [
        //             'exception' => $e->getMessage(),
        //             'response' => $responseContent
        //         ]);
        //     } else {
        //         $this->logger->error('Error processing Shift4 payment', ['exception' => $e->getMessage()]);
        //     }
        
        //     throw $e;
        // }
    }   
}
