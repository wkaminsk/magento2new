<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;

class ProcessSuccessfulPost implements ObserverInterface
{
    /**
     * @var Log
     */
    private $logger;

    /**
     * @var OrderApi
     */
    private $orderApi;

    /**
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
        $this->logger->log(__("Running Successful Post Observer for Order: #%1", $order->getIncrementId()), 2);
        $response = $observer->getResponse();
        if (isset($response->order)) {
            $orderId = $response->order->id;
            $status = $response->order->status;
            $oldStatus = isset($response->order->old_status) ? $response->order->old_status : null;
            $description = isset($response->order->description) ? $response->order->description : null;

            if (!$description) {
                $description = "Riskified Status: $status";
            }

            if ($orderId && $status) {
                $this->orderApi->update($order, $status, $oldStatus, $description);
            }
        }
    }
}
