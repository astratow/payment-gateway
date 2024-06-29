<?PHP
// src/Controller/PaymentController.php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Service\PaymentGatewayService;

class PaymentController extends AbstractController
{
    private PaymentGatewayService $paymentGatewayService;

    public function __construct(PaymentGatewayService $paymentGatewayService)
    {
        $this->paymentGatewayService = $paymentGatewayService;
    }

    /**
     * @Route("/app/example/{gateway}", name="app_example", methods={"POST"})
     */
    public function example(string $gateway, Request $request): JsonResponse
    {
        $paymentData = json_decode($request->getContent(), true);

        try {
            $response = $this->paymentGatewayService->processPayment($gateway, $paymentData);
            return new JsonResponse($response);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 400);
        }
    }
}