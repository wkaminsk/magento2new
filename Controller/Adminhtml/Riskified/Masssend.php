<?php

namespace Riskified\Decider\Controller\Adminhtml\Riskified;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;

class Masssend extends Action
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

    public function execute()
    {
        $this->logger->log(__("Initializing Masssend action"), 2);
        $ids = $this->getRequest()->getParam('selected');
        $sendCount = $this->apiOrderLayer->sendOrders($ids);
        $this->messageManager->addSuccess(
            __('%1 order(s) was sent to Riskified', $sendCount)
        );
        $this->logger->log(__("Sent order(s) with ids: %1", $ids), 2);
        $this->_redirect("sales/order");
    }
}
