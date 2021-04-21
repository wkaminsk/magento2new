<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Magento\Sales\Api\OrderRepositoryInterface;

class SalesOrderAddressUpdate implements ObserverInterface
{
    /**
     * @var Log
     */
    private $logger;

    /**
     * @var OrderApi
     */
    private $apiOrder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Log $logger
     * @param OrderApi $orderApi
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Log $logger,
        OrderApi $orderApi,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->apiOrder = $orderApi;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try {
            $order_id = $observer->getOrderId();
            $order = $this->orderRepository->get($order_id);

            if (!$order) {
                return;
            }

            $this->logger->log(__("Running update order address, order: #%1", $order->getIncrementId(), 2));

            $this->apiOrder->post($order, Api::ACTION_UPDATE);
        } catch (\Exception $e) {
            $this->logger->logException($e);
        }
    }
}
