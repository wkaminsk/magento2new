<?php

namespace Riskified\Decider\Controller\Response;
use Magento\Framework\Controller\ResultFactory;
use Riskified\Decider\Model\Api\Log;

class Session extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;

    /**
     * @var Log
     */
    private $logger;

    /**
     * Session constructor.
     *
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        Log $logger
    ) {
        parent::__construct($context);
        $this->customerSession = $customerSession;
        $this->resultFactory = $context->getResultFactory();
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $this->logger->log(__('Getting sessionId'), 2);
        $result = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $payload = ['session_id' => $this->customerSession->getSessionId()];
        $this->logger->log(sprintf(__('Return payload: %s'), serialize($payload)), 2);
        $result = $result->setData($payload);

        return $result;
    }
}
