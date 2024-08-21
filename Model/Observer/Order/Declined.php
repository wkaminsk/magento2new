<?php

namespace Riskified\Decider\Model\Observer\Order;

use Magento\Framework\Event\Observer;
use Magento\Framework\Exception\NoSuchEntityException;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Email\Declined as DeclinedTransport;

class Declined
{

    /**
     * Order declined event listener constructor.
     *
     * @param DeclinedTransport $declinedTransport
     * @param Config $apiConfig
     */
    public function __construct(
        private declinedTransport $declinedTransport,
        private readonly Config $apiConfig
    ) {
    }

    /**
     * Observer execute
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function execute(\Magento\Framework\Event\Observer $observer): static
    {
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $observer->getOrder();

        if (!$this->apiConfig->isDeclineNotificationEnabled()) {
            return $this;
        }

        if ($order->getDeclineNotificationSent()) {
            return $this;
        }

        $this->declinedTransport->send($order);

        return $this;
    }
}
