<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;

class SalesOrderShipmentSaveAfter implements ObserverInterface
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
        $shipment = $observer->getShipment();
        $this->logger->log(__("Running order fulfillment, shipment #%1", $shipment->getIncrementId()), 2);
        try {
            $this->apiOrderLayer->post($shipment, Api::ACTION_FULFILL);
        } catch (\Exception $e) {
            $this->logger->log(
                sprintf(
                    __("Order fulfilment was not able to sent. Order #%s, shipment #%s"),
                    $shipment->getOrder()->getIncrementId(),
                    $shipment->getIncrementId()
                ),
                2
            );
        }
    }
}
