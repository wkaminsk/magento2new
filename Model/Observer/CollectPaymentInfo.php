<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Log;

class CollectPaymentInfo implements ObserverInterface
{
    /**
     * @var Log
     */
    private $logger;

    /**
     * @param Log $logger
     */
    public function __construct(Log $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $this->logger->log(__("Collect Payment Info action"), 2);
        $payment = $observer->getQuote()->getPayment();
        $cc_bin = substr($payment->getCcNumber(), 0, 6);
        if ($cc_bin) {
            $payment->setAdditionalInformation('riskified_cc_bin', $cc_bin);
            $this->logger->log(__("Added riskified_cc_bin = %1", $cc_bin), 2);
        }
    }
}
