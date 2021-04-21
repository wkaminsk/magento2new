<?php

namespace Riskified\Decider\Controller\Adminhtml\Riskified;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;

class Send extends Action
{
    /**
     * @var OrderApi
     */
    protected $apiOrderLayer;

    /**
     * @var Log
     */
    private $logger;

    /**
     * @param Context $context
     * @param OrderApi $apiOrderLayer
     */
    public function __construct(
        Context $context,
        OrderApi $apiOrderLayer,
        Log $logger
    ) {
        $this->apiOrderLayer = $apiOrderLayer;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute.
     */
    public function execute()
    {
        $this->logger->log(__("Initializing send order action"), 2);
        $id = $this->getRequest()->getParam('order_id');
        $this->apiOrderLayer->sendOrders([$id]);
        $this->logger->log(__("Order with id: %1 was sent", $id), 2);
        $this->_redirect("sales/order/view", ['order_id' => $id]);
    }
}
