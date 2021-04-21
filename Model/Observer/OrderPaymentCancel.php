<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as ApiOrder;

class OrderPaymentCancel implements ObserverInterface
{
    /**
     * @var LogApi
     */
    private $logger;

    /**
     * @var ApiOrder
     */
    private $apiOrderLayer;

    /**
     * @param LogApi $logger
     * @param ApiOrder $orderApi
     */
    public function __construct(
        LogApi $logger,
        ApiOrder $orderApi
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
        $this->logger->log(__("Cancel order payment for order #%1", $order->getIncrementId()), 2);
        try {
            $this->apiOrderLayer->post($order, Api::ACTION_CANCEL);
        } catch (\Exception $e) {
            $this->logger->logException($e);
        }
    }
}
