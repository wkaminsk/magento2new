<?php

namespace Riskified\Decider\Plugin\Email;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Model\Order\Email\Sender\OrderSender\Interceptor as Subject;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Email\Declined;

class OrderSender {

    public function __construct(
        private readonly Declined $declined,
        private readonly Config $apiConfig,
        private readonly \Magento\Framework\App\Config\ScopeConfigInterface $globalConfig
    ) {
    }

    /**
     * @throws NoSuchEntityException
     */
    public function afterSend(Subject $subject, $result, \Magento\Sales\Model\Order $order)
    {
        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            return $this;
        }

        if (!$this->apiConfig->isDeclineNotificationEnabled()) {
            return $this;
        }

        if ($order->getDeclineNotificationSent()) {
            return $this;
        }

        foreach ($order->getStatusHistories() as $history) {
            if (str_contains($history->getComment(), 'Order exhibits data points')) {
                $this->declined->send($order);
            }
        }
    }
}
