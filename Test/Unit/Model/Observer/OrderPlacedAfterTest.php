<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\GroupRepository;
use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\HTTP\Header;
use Magento\Framework\Locale\ResolverInterface;
use Magento\Framework\Logger\Monolog;
use Magento\Framework\Registry;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Api\Order\PaymentProcessor\AbstractPayment;
use Riskified\Decider\Model\Api\Order\PaymentProcessorFactory;
use Riskified\Decider\Model\Observer\OrderPlacedAfter;
use Riskified\OrderWebhook\Transport\CurlTransport;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use \Magento\Framework\Event\Manager as EventManager;
use \Magento\Backend\Model\Auth\Session;
use \Magento\Framework\Message\ManagerInterface;
use \Magento\Framework\Stdlib\DateTime\DateTime;
use \Riskified\Decider\Model\QueueFactory;
use \Magento\Sales\Model\OrderRepository;
use \Magento\Checkout\Model\Session as CheckoutSession;
use \Magento\Framework\Session\SessionManager;
use \Magento\Framework\Api\SearchCriteriaBuilder;
use \Riskified\Decider\Model\Api\Order\Log as ApiOrderLog;
use \Riskified\Decider\Model\Api\Order\Helper;
use \Magento\Sales\Model\ResourceModel\Order\Payment\Collection as PaymentCollection;
use \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory as AddressCollectionFactory;
use \Magento\Sales\Model\Order\Payment;

class OrderPlacedAfterTest extends TestCase
{
    /** @var OrderPlacedAfter */
    protected $object;
    private $paymentProcessorFactory;

    public function setUp()
    {
        $logger = $this->createMock(OrderLogger::class);

        $groupRepository = $this->createMock(GroupRepository::class);
        $customerFactory = $this->createMock(Customer::class);
        $monologLogger = $this->createMock(Monolog::class);
        $orderFactory = $this->createMock(OrderCollectionFactory::class);
        $storeManager = $this->createMock(StoreManagerInterface::class);
        $categoryRepository = $this->createMock(CategoryRepositoryInterface::class);

        $this->paymentProcessorFactory = $this->getMockBuilder(PaymentProcessorFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $state = $this->createMock(State::class);
        $localeResolver = $this->createMock(ResolverInterface::class);
        $httpHeader = $this->createMock(Header::class);
        $registry = $this->createMock(Registry::class);

        $apiConfig = $this->createConfiguredMock(Config::class, ['isEnabled' => true]);
        $eventManager = $this->createMock(EventManager::class);
        $helperContext = $this->createConfiguredMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getEventManager' => $eventManager]
        );
        $backendSession = $this->createMock(Session::class);
        $messageManager = $this->createMock(ManagerInterface::class);
        $date = $this->createMock(DateTime::class);
        $queueFactory = $this->createMock(QueueFactory::class);

        $this->orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $checkoutSession = $this->createMock(CheckoutSession::class);
        $sessionManager = $this->createMock(SessionManager::class);
        $searchCriteriaBuilder = $this->createMock(SearchCriteriaBuilder::class);
        $curlTransport = $this->createMock(CurlTransport::class);

        $apiMock = $this->getMockBuilder(Api::class)
            ->setMethods(['initSdk', 'parseRequest', 'getTransport'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $apiMock->method('getTransport')->willReturn($curlTransport);

        $orderRepository = $this->getMockBuilder(OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $orderLog = $this->createMock(ApiOrderLog::class);

        $helper = new Helper(
            $groupRepository,
            $customerFactory,
            $monologLogger,
            $apiConfig,
            $orderLog,
            $messageManager,
            $customerFactory,
            $orderFactory,
            $storeManager,
            $categoryRepository,
            $this->paymentProcessorFactory,
            $state,
            $localeResolver,
            $httpHeader,
            $registry
        );

        $orderApiModel = new OrderApi(
            $apiMock,
            $helper,
            $apiConfig,
            $orderLog,
            $helperContext,
            $backendSession,
            $messageManager,
            $date,
            $queueFactory,
            $orderRepository,
            $checkoutSession,
            $sessionManager,
            $searchCriteriaBuilder
        );

        $this->object = new OrderPlacedAfter($logger, $orderApiModel);
    }

    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder']);

        $paymentCollection = $this->createPartialMock(PaymentCollection::class, ['getItems']);
        $addressCollection = $this->createPartialMock(AddressCollectionFactory::class, ['getItems']);

        $payment = $this->createPartialMock(Payment::class, ['setOrder', 'getMethod']);

        $dataObject = $this->createPartialMock(
            Order::class,
            ['dataHasChangedFor','getId', 'getPaymentsCollection', 'save', 'getStatusHistoryCollection', 'getAddressesCollection', 'getAddresses', 'getItems']
        );

        $paymentCollection->method('getItems')->willReturn([$payment]);
        $payment->method('getMethod')->willReturn('checkmo');
        $dataObject->method('getStatusHistoryCollection')->willReturn([]);
        $dataObject->method('getAddressesCollection')->willReturn($addressCollection);
        $dataObject->method('getAddresses')->willReturn([]);
        $dataObject->method('getItems')->willReturn([]);

        $paymentProcessor = $this->getMockBuilder(AbstractPayment::class)
            ->setMethods(['getDetails'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $this->paymentProcessorFactory
            ->method('create')
            ->with($dataObject)
            ->willReturn($paymentProcessor);

        $dataObject
            ->method('dataHasChangedFor')
            ->with('state')
            ->willReturn(true);

        $dataObject
            ->method('getPaymentsCollection')
            ->willReturn($paymentCollection);

        $observer
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn(
                $dataObject
            );

        $this->object->execute($observer);
    }
}
