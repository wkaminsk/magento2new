<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use Riskified\Decider\Model\Api\Order as OrderApi;

class OrderPlacedAfter implements ObserverInterface
{
    /**
     * @var OrderLogger
     */
    private $logger;

    /**
     * @var OrderApi
     */
    private $orderApi;

    /**
     * OrderPlacedAfter constructor.
     *
     * @param Log $logger
     * @param OrderApi $orderApi
     */
    public function __construct(
        Log $logger,
        OrderApi $orderApi
    ) {
        $this->logger = $logger;
        $this->orderApi = $orderApi;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        if (!$order) {
            return;
        }

        $this->logger->log(__("After Place order Observer for order #%1", $order->getIncrementId()), 2);

        if ($order->dataHasChangedFor('state')) {
            try {
                $this->orderApi->post($order, Api::ACTION_UPDATE);
            } catch (\Exception $e) {
                $this->logger->logException($e);
            }
        } else {
            $this->logger->log(__("No data found"), 2);
        }
    }
}
