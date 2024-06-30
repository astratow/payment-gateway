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

        $username = $_ENV['SHIFT4_API_USERNAME'];
        $password = $_ENV['SHIFT4_API_PASSWORD'];

        $requestPayload = [
            'amount' => $paymentData['amount'],
            'currency' => $paymentData['currency'],
            'card' => [
                'number' => $paymentData['card_number'],
                'expMonth' => $paymentData['exp_month'],
                'expYear' => $paymentData['exp_year'],
                'cvc' => $paymentData['card_cvv'],
            ]
        ];

        $this->logger->info('Shift4 request payload', ['payload' => $requestPayload]);

        try {
            $response = $this->client->request('POST', 'https://api.shift4.com/charges', [
                'json' => $requestPayload,
                'auth_basic' => [$username, $password ?: ''],
            ]);

            $responseData = $response->toArray();
            $this->logger->info('Shift4 response', ['response' => $responseData]);

            return $responseData;
        } catch (\Exception $e) {
            if ($e instanceof \Symfony\Contracts\HttpClient\Exception\HttpExceptionInterface) {
                $response = $e->getResponse();
                $responseContent = $response->getContent(false);
                $this->logger->error('Error processing Shift4 payment', [
                    'exception' => $e->getMessage(),
                    'response' => $responseContent
                ]);
            } else {
                $this->logger->error('Error processing Shift4 payment', ['exception' => $e->getMessage()]);
            }

            throw $e;
        }
    }   
}
