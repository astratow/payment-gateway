<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use App\Service\PaymentGatewayService;
use Psr\Log\LoggerInterface;

class PaymentCommand extends Command
{
    protected static $defaultName = 'app:example';

    private PaymentGatewayService $paymentGatewayService;
    private LoggerInterface $logger;

    public function __construct(PaymentGatewayService $paymentGatewayService, LoggerInterface $logger)
    {
        parent::__construct();
        $this->paymentGatewayService = $paymentGatewayService;
        $this->logger = $logger;
    }

    protected function configure()
    {
        $this
            ->setDescription('Process a payment through a specified gateway.')
            ->addArgument('gateway', InputArgument::REQUIRED, 'The payment gateway (aci or shift4).')
            ->addOption('amount', null, InputOption::VALUE_REQUIRED, 'The amount.')
            ->addOption('currency', null, InputOption::VALUE_REQUIRED, 'The currency.')
            ->addOption('card_number', null, InputOption::VALUE_REQUIRED, 'The card number.')
            ->addOption('card_exp_year', null, InputOption::VALUE_REQUIRED, 'The card expiry year.')
            ->addOption('card_exp_month', null, InputOption::VALUE_REQUIRED, 'The card expiry month.')
            ->addOption('card_cvv', null, InputOption::VALUE_REQUIRED, 'The card CVV.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $gateway = $input->getArgument('gateway');
        $paymentData = [
            'amount' => $input->getOption('amount'),
            'currency' => $input->getOption('currency'),
            'card_number' => $input->getOption('card_number'),
            'card_exp_year' => $input->getOption('card_exp_year'),
            'card_exp_month' => $input->getOption('card_exp_month'),
            'card_cvv' => $input->getOption('card_cvv'),
        ];

        $this->logger->info('Executing payment command', ['gateway' => $gateway, 'data' => $paymentData]);

        try {
            $response = $this->paymentGatewayService->processPayment($gateway, $paymentData);
            $output->writeln(json_encode($response));
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->logger->error('Error executing payment command', ['exception' => $e->getMessage()]);
            $output->writeln('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
