<?php

namespace Riskified\Decider\Model\Api;

use Riskified\Decider\Model\Config\Source\LogOptions;
use Riskified\Decider\Model\Logger\Order as OrderLogger;

class Log
{
    /**
     * @var OrderLogger
     */
    private $logger;

    /**
     * @var Config
     */
    private $config;

    /**
     * Log constructor.
     *
     * @param OrderLogger $logger
     */
    public function __construct(OrderLogger $logger, Config $config)
    {
        $this->logger = $logger;
        $this->config = $config;
    }

    /**
     * @param $model
     */
    public function payment($model)
    {
        $this->logger->debug("Payment info debug Logs:");
        try {
            $payment = $model->getPayment();
            $gateway_name = $payment->getMethod();
            $this->logger->debug("Payment Gateway: " . $gateway_name);
            $this->logger->debug("payment->getCcLast4(): " . $payment->getCcLast4());
            $this->logger->debug("payment->getCcType(): " . $payment->getCcType());
            $this->logger->debug("payment->getCcCidStatus(): " . $payment->getCcCidStatus());
            $this->logger->debug("payment->getCcAvsStatus(): " . $payment->getCcAvsStatus());
            $this->logger->debug("payment->getAdditionalInformation(): " . PHP_EOL . var_export($payment->getAdditionalInformation(), 1));
            $sage = $model->getSagepayInfo();

            if (is_object($sage)) {
                $this->logger->debug("sagepay->getLastFourDigits(): " . $sage->getLastFourDigits());
                $this->logger->debug("sagepay->last_four_digits: " . $sage->getData('last_four_digits'));
                $this->logger->debug("sagepay->getCardType(): " . $sage->getCardType());
                $this->logger->debug("sagepay->card_type: " . $sage->getData('card_type'));
                $this->logger->debug("sagepay->getAvsCv2Status: " . $sage->getAvsCv2Status());
                $this->logger->debug("sagepay->address_result: " . $sage->getData('address_result'));
                $this->logger->debug("sagepay->getCv2result: " . $sage->getCv2result());
                $this->logger->debug("sagepay->cv2result: " . $sage->getData('cv2result'));
                $this->logger->debug("sagepay->getAvscv2: " . $sage->getAvscv2());
                $this->logger->debug("sagepay->getAddressResult: " . $sage->getAddressResult());
                $this->logger->debug("sagepay->getPostcodeResult: " . $sage->getPostcodeResult());
                $this->logger->debug("sagepay->getDeclineCode: " . $sage->getDeclineCode());
                $this->logger->debug("sagepay->getBankAuthCode: " . $sage->getBankAuthCode());
                $this->logger->debug("sagepay->getPayerStatus: " . $sage->getPayerStatus());
            }
            if ($gateway_name == "optimal_hosted") {
                $optimalTransaction = unserialize($payment->getAdditionalInformation('transaction'));
                if ($optimalTransaction) {
                    $this->logger->debug("Optimal transaction: ");
                    $this->logger->debug("transaction->cvdVerification: " . $optimalTransaction->cvdVerification);
                    $this->logger->debug("transaction->houseNumberVerification: " . $optimalTransaction->houseNumberVerification);
                    $this->logger->debug("transaction->zipVerification: " . $optimalTransaction->zipVerification);
                } else {
                    $this->logger->debug("Optimal gateway but no transaction found");
                }
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
    }

    /**
     * @param $message
     * @param int $level
     */
    public function log($message, int $level = 1)
    {
        $logLevel = $this->config->getLoggingOptionLevel();

        switch (true) {
            case ($logLevel === LogOptions::STANDARD_LOG_OPTION && $level === LogOptions::STANDARD_LOG_OPTION):
                $this->logger->addInfo($message);
                break;
            case ($logLevel === LogOptions::RICHFULL_LOG_OPTION && $level >= LogOptions::STANDARD_LOG_OPTION):
                $this->logger->notice($message);
                break;
        }
    }

    /**
     * @param $message
     */
    public function logException($message)
    {
        $logLevel = $this->config->getLoggingOptionLevel();

        if ($logLevel >= LogOptions::STANDARD_LOG_OPTION) {
            $this->logger->addCritical($message);
        }
    }
}
