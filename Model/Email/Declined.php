<?php

namespace Riskified\Decider\Model\Email;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\Context;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\Service\InvoiceService;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Api\Order\Log;
use Riskified\Decider\Model\Email\Declined as DeclinedTransport;
use Riskified\Decider\Model\Logger\Order;
use \Magento\Sales\Api\OrderRepositoryInterface;

class Declined
{
    /**
     * Module main logger class.
     *
     * @var Order
     */
    protected Order $logger;

    /**
     * Module api class.
     *
     * @var OrderApi
     */
    protected OrderApi $apiOrder;

    /**
     * Api logger.
     *
     * @var Log
     */
    protected Log $apiOrderLogger;

    /**
     * Module config.
     *
     * @var Config
     */
    protected Config $apiConfig;

    /**
     * Magento's invoice service.
     *
     * @var InvoiceService
     */
    protected InvoiceService $invoiceService;

    /**
     * Context class.
     *
     * @var Context
     */
    protected Context $context;

    /**
     * State class used to emulate admin scope during invoice creation.
     *
     * @var State
     */
    protected State $state;
    private DeclinedTransport $declinedTransport;
    private ScopeConfigInterface $globalConfig;
    private $inlineTranslation;
    private $transportBuilder;

    /**
     * Order declined event listener constructor.
     *
     * @param Log $apiOrderLogger
     * @param Order $logger
     * @param Config $apiConfig
     * @param OrderApi $orderApi
     * @param InvoiceService $invoiceService
     * @param Context $context
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     * @param StoreManagerInterface $storeManager
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $globalConfig
     */
    public function __construct(
        Log $apiOrderLogger,
        Order $logger,
        Config $apiConfig,
        OrderApi $orderApi,
        InvoiceService $invoiceService,
        Context $context,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        StoreManagerInterface $storeManager,
        OrderRepositoryInterface $orderRepository,
    ) {
        $this->logger = $logger;
        $this->context = $context;
        $this->apiOrder = $orderApi;
        $this->apiConfig = $apiConfig;
        $this->apiOrderLogger = $apiOrderLogger;
        $this->invoiceService = $invoiceService;
        $this->state = $context->getAppState();
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->storeManager = $storeManager;
        $this->orderRepository = $orderRepository;
    }

    /**
     * Observer execute
     *
     * @param Observer $observer
     *
     * @return $this
     * @throws NoSuchEntityException
     */
    public function send(OrderInterface $order): static
    {
        if (!$this->apiConfig->isDeclineNotificationEnabled()) {
            return $this;
        }

        if ($order->getDeclineNotificationSent()) {
            return $this;
        }

        if ($this->globalConfig->getValue('sales_email/general/async_sending')) {
            return $this;
        }

        $subject = $this->apiConfig->getDeclineNotificationSubject($order->getStoreId());
        $content = $this->apiConfig->getDeclineNotificationContent($order->getStoreId());

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

            $this->logger->info($fileLog);

            $order
                ->addStatusHistoryComment($orderComment)
                ->setIsCustomerNotified(true);

            $order->setDeclineNotificationSent(true);

            $this->orderRepository->save($order);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $this;
    }


    private function getFormattedData($order): array
    {
        $products = [];

        foreach ($order->getAllItems() as $item) {
            $products[] = $item->getName();
        }

        return [
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
    }
}
