<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;

class OrderPaymentVoid implements ObserverInterface
{
    /**
     * @var LogApi
     */
    private $logger;

    /**
     * @var OrderApi
     */
    private $apiOrderLayer;

    /**
     * OrderPaymentVoid constructor.
     *
     * @param LogApi $logger
     * @param OrderApi $orderApi
     */
    public function __construct(
        LogApi $logger,
        OrderApi $orderApi
    ) {
        $this->logger = $logger;
        $this->apiOrderLayer = $orderApi;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getPayment()->getOrder();
        $this->logger->log(__("Running Void Payment for order #%1", $order->getIncrementId()), 2);
        $this->apiOrderLayer->post($order, Api::ACTION_CANCEL);
    }
}
