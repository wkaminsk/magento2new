<?php
namespace Riskified\Decider\Plugin;

use Magento\Framework\Registry;
use Magento\Sales\Api\Data\OrderInterface;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use Riskified\Decider\Model\Api\Order as OrderApi;

class ObserveOrderSavePlugin {
    public function __construct(
        OrderLogger $logger,
        OrderApi $orderApi,
        Registry $registry
    ) {
        $this->_logger = $logger;
        $this->_orderApi = $orderApi;
        $this->registry = $registry;
    }

    public function afterSave(
        \Magento\Sales\Model\ResourceModel\Order $subject,
         $result,$object
    ){
        $oldData = $object->getOrigData('status');
        $newData = $object->getData('status');

        if ($this->registry->registry("riskified-order")) {
            return $this;
        }

        if ($oldData != $newData && $newData == "pending_payment") {
            $this->registry->register("riskified-order", $object, true);
            $this->registry->register("riskified-place-order-after", true, true);

            $this->_orderApi->post($object, Api::ACTION_UPDATE);
        }
    }
}