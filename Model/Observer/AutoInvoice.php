<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\Context;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\InvoiceRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderEntity;
use Magento\Sales\Model\Service\InvoiceService;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Api\Order\Log as OrderLog;

/**
 * Observer Auto Invoice Class.
 * Creates invoice when order was approved in Riskified.
 *
 * @category Riskified
 * @package  Riskified_Decider
 */
class AutoInvoice implements ObserverInterface
{
    /**
     * Module main logger class.
     *
     * @var Log
     */
    private $logger;

    /**
     * Module api class.
     *
     * @var OrderApi
     */
    private $apiOrder;

    /**
     * Api logger.
     *
     * @var OrderLog
     */
    private $apiOrderLogger;

    /**
     * Module config.
     *
     * @var Config
     */
    private $apiConfig;

    /**
     * Magento's invoice service.
     *
     * @var InvoiceService
     */
    private $invoiceService;

    /**
     * Context class.
     *
     * @var Context
     */
    private $context;

    /**
     * State class used to emulate admin scope during invoice creation.
     *
     * @var State
     */
    private $state;

    /**
     * Order repository class.
     *
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * Invoice repository class.
     *
     * @var InvoiceRepositoryInterface
     */
    private $invoiceRepository;


    /**
     * @param OrderLog $apiOrderLogger
     * @param Log $logger
     * @param Config $apiConfig
     * @param InvoiceService $invoiceService
     * @param Context $context
     * @param OrderRepositoryInterface $orderRepository
     * @param InvoiceRepositoryInterface $invoiceRepository
     */
    public function __construct(
        OrderLog $apiOrderLogger,
        Log $logger,
        Config $apiConfig,
        InvoiceService $invoiceService,
        Context $context,
        OrderRepositoryInterface $orderRepository,
        InvoiceRepositoryInterface $invoiceRepository
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->apiConfig = $apiConfig;
        $this->apiOrderLogger = $apiOrderLogger;
        $this->invoiceService = $invoiceService;
        $this->state = $context->getAppState();
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
    }

    /**
     * Main method ran during event raise.
     *
     * @param Observer $observer
     *
     * @return bool
     */
    public function execute(Observer $observer)
    {
        if (!$this->canRun()) {
            return false;
        }

        /** @var OrderInterface $order */
        $order = $observer->getOrder();

        if (!$order || !$order->getId()) {
            return false;
        }
        $this->logger->log(sprintf(__('Auto-invoicing  order #%s'), $order->getIncrementId()), 2);

        if (!$order->canInvoice() || $order->getState() != OrderEntity::STATE_PROCESSING) {
            $this->logger->log('Order cannot be invoiced');
            if ($this->apiConfig->isLoggingEnabled()) {
                $this->apiOrderLogger->logInvoice($order);
            }

            return false;
        }

        $invoice = $this->state->emulateAreaCode(
            'adminhtml',
            [$this->invoiceService, 'prepareInvoice'],
            [$order]
        );

        if (!$invoice->getTotalQty()) {
            $this->logger->log(__('Cannot create an invoice without products'));

            return false;
        }
        try {
            $invoice
                ->setRequestedCaptureCase($this->apiConfig->getCaptureCase())
                ->addComment(
                    __(
                        'Invoice automatically created by '
                        . 'Riskified when order was approved'
                    ),
                    false,
                    false
                );

            $this->state->emulateAreaCode('adminhtml', [$invoice, 'register']);
        } catch (\Exception $e) {
            $this->logger->logException(sprintf(__("Error creating invoice: %s"), $e->getMessage()));
            return false;
        }
        try {
            $this->invoiceRepository->save($invoice);
            if ($order->getState() != OrderEntity::STATE_PROCESSING) {
                $this->orderRepository->save($invoice->getOrder());
            }
        } catch (\Exception $e) {
            $this->logger->logException(sprintf(__('Error creating transaction: %s'), $e->getMessage()));

            return false;
        }
        $this->logger->log(__("Transaction saved"));
    }

    /**
     * Method checks if observer can be run
     *
     * @return bool
     */
    private function canRun()
    {
        if (!$this->apiConfig->isAutoInvoiceEnabled()) {
            return false;
        }
        if (!$this->apiConfig->isEnabled()) {
            return false;
        }

        return true;
    }
}
