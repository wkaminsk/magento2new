<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Magento\Framework\Registry;
use Magento\Framework\Event\Observer;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Logger\Order as OrderLogger;

class OrderSaveAfter implements ObserverInterface
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
     * @var Registry
     */
    protected $registry;

    /**
     * @param Log $logger
     * @param OrderApi $orderApi
     * @param  $registry
     */
    public function __construct(
        Log $logger,
        OrderApi $orderApi,
        Registry $registry
    ) {
        $this->logger = $logger;
        $this->orderApi = $orderApi;
        $this->registry = $registry;
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

        $this->logger->log(__("After Save Order Observer, Order: #%1", $order->getIncrementId()), 2);

        $newState = $order->getState();

        if ((int)$order->dataHasChangedFor('state') === 1) {
            $oldState = $order->getOrigData('state');

            if ($oldState == Order::STATE_HOLDED and $newState == Order::STATE_PROCESSING) {
                $this->logger->log(__("Order : " . $order->getId() . " not notifying on unhold action"));
                return;
            }

            $this->logger->log(__("Order: " . $order->getId() . " state changed from: " . $oldState . " to: " . $newState));

            // if we posted we should not re post
            if ($this->registry->registry("riskified-order")) {
                $this->logger->log(__("Order : " . $order->getId() . " is already riskifiedInSave"));
                return;
            }

            try {
                if (!$this->registry->registry("riskified-order")) {
                    $this->registry->register("riskified-order", $order);
                }
                $this->orderApi->post($order, Api::ACTION_UPDATE);

                $this->registry->unregister("riskified-order");
            } catch (\Exception $e) {
                // There is no need to do anything here. The exception has already been handled and a retry scheduled.
                // We catch this exception so that the order is still saved in Magento.
            }
        } else {
            $this->logger->log(
                sprintf(
                    __("Order: %s state didn't change on save - not posting again: %s"),
                    $order->getIncrementId(),
                    $newState
                )
            );
        }
    }
}
