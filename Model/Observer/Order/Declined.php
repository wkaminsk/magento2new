<?php

namespace Riskified\Decider\Model\Observer\Order;

use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Model\Context;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Store\Model\StoreManagerInterface;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order as OrderApi;

class Declined implements ObserverInterface
{
    /**
     * @var Log
     */
    protected $logger;

    /**
     * Module api class.
     *
     * @var OrderApi
     */
    protected $apiOrder;

    /**
     * Module config.
     *
     * @var Config
     */
    protected $apiConfig;

    /**
     * Magento's invoice service.
     *
     * @var InvoiceService
     */
    protected $invoiceService;

    /**
     * Context class.
     *
     * @var Context
     */
    protected $context;

    /**
     * State class used to emulate admin scope during invoice creation.
     *
     * @var State
     */
    protected $state;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Log $logger
     * @param Config $apiConfig
     * @param OrderApi $orderApi
     * @param InvoiceService $invoiceService
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Log $logger,
        Config $apiConfig,
        OrderApi $orderApi,
        InvoiceService $invoiceService,
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->apiOrder = $orderApi;
        $this->apiConfig = $apiConfig;
        $this->invoiceService = $invoiceService;
        $this->state = $context->getAppState();
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * @param Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getOrder();

        $this->logger->log(__("Initializing Decline for Order: #%1", $order->getIncrementId()), 2);

        if (!$this->apiConfig->isDeclineNotificationEnabled()) {
            return;
        }

        if ($order->getDeclineNotificationSent()) {
            return;
        }

        $subject = $this->apiConfig->getDeclineNotificationSubject();
        $content = $this->apiConfig->getDeclineNotificationContent();

        $shortCodes = [
            "{{customer_name}}",
            "{{customer_firstname}}",
            "{{order_increment_id}}",
            "{{order_view_url}}",
            "{{products}}",
            "{{store_name}}",
        ];
        $formattedPayload = $this->getFormattedData($order);

        foreach ($shortCodes as $key => $value) {
            $subject = str_replace($value, $formattedPayload[$key], $subject);
            $content = str_replace($value, $formattedPayload[$key], $content);
        }

        try {
            if ($content == "") {
                throw new \Exception("Email content is empty");
            }

            if ($subject == "") {
                throw new \Exception("Email subject is empty");
            }

            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier('riskified_order_declined') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => $order->getStoreId(),
                    ]
                )
                ->setTemplateVars(
                    [
                        "content" => $content,
                        "subject" => $subject,
                    ]
                )
                ->setFrom(
                    [
                        "email" => $this->apiConfig->getDeclineNotificationSenderEmail(),
                        "name" => $this->apiConfig->getDeclineNotificationSenderName(),
                    ]
                )
                ->addTo($order->getCustomerEmail(), $order->getCustomerName())
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $fileLog = sprintf(
                __("Declination email was sent to customer %s (%s) for order #%s"),
                $order->getCustomerName(),
                $order->getCustomerEmail(),
                $order->getIncrementId()
            );

            $orderComment = sprintf(
                __("Declination email was sent to customer %s (%s)"),
                $order->getCustomerName(),
                $order->getCustomerEmail()
            );

            $this->logger->log($fileLog, 2);

            $order
                ->addStatusHistoryComment($orderComment)
                ->setIsCustomerNotified(true);

            $order->setDeclineNotificationSent(true);

            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->logException($e->getMessage());
        }
    }

    /**
     * @param $order
     *
     * @return array
     */
    private function getFormattedData($order)
    {
        $products = [];

        foreach ($order->getAllItems() as $item) {
            $products[] = $item->getName();
        }

        $data = [
            $order->getCustomerName(),
            $order->getCustomerFirstname(),
            $order->getIncrementId(),
            $this->storeManager->getStore()->getUrl(
                "sales/order/view",
                [
                    "order_id" => $order->getId(),
                    "_secure" => true
                ]
            ),
            join(', ', $products),
            $this->storeManager->getStore()->getName()
        ];

        return $data;
    }
}
