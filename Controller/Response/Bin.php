<?php

namespace Riskified\Decider\Controller\Response;

use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Riskified\Decider\Model\Api\Log;

class Bin extends Action
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var Log
     */
    private $logger;
    /**
     * @var Context
     */
    private $context;

    /**
     * @param Context $context
     * @param CheckoutSession $checkoutSession
     * @param Log $logger
     */
    public function __construct(
        Context $context,
        CheckoutSession $checkoutSession,
        Log $logger
    ) {
        parent::__construct($context);
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->context = $context;
    }

    /**
     * Execute.
     */
    public function execute()
    {
        $card_no = $this->getRequest()->getParam('card', null);
        $this->logger->log(sprintf(__("Set RiskifiedBin: %s"), $card_no), 2);
        $this->checkoutSession->setRiskifiedBin($card_no);
    }
}
