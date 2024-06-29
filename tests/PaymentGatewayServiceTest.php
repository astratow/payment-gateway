<?PHP

// tests/PaymentGatewayServiceTest.php
namespace App\Tests;

use PHPUnit\Framework\TestCase;
use App\Service\PaymentGatewayService;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class PaymentGatewayServiceTest extends TestCase
{
    public function testProcessPaymentShift4()
    {
        $client = new MockHttpClient([
            new MockResponse(json_encode([
                'id' => 'tx_123456',
                'createdAt' => '2023-06-25T12:34:56Z',
                'amount' => 100,
                'currency' => 'USD',
                'card' => ['number' => '4111111111111111'],
            ]))
        ]);
        $service = new PaymentGatewayService($client);

        $response = $service->processPayment('shift4', [
            'amount' => 100,
            'currency' => 'USD',
            'card_number' => '4111111111111111',
            'card_exp_year' => '2025',
            'card_exp_month' => '12',
            'card_cvv' => '123',
        ]);

        $this->assertEquals('tx_123456', $response['transaction_id']);
    }

    public function testProcessPaymentAci()
    {
        $client = new MockHttpClient([
            new MockResponse(json_encode([
                'transactionId' => 'tx_654321',
                'timestamp' => '2023-06-25T12:34:56Z',
                'amount' => 200,
                'currency' => 'EUR',
                'card' => ['number' => '4111111111111111'],
            ]))
        ]);
        $service = new PaymentGatewayService($client);

        $response = $service->processPayment('aci', [
            'amount' => 200,
            'currency' => 'EUR',
            'card_number' => '4111111111111111',
            'card_exp_year' => '2025',
            'card_exp_month' => '12',
            'card_cvv' => '123',
        ]);

        $this->assertEquals('tx_654321', $response['transaction_id']);
    }
}
