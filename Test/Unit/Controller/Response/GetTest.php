<?php

namespace Riskified\Decider\Test\UnitController\Response;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Sales\Model\Order as SalesOrder;
use PHPUnit\Framework\MockObject\MockObject;
use Riskified\Decider\Controller\Response\Get;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Order;

class GetTest extends \PHPUnit\Framework\TestCase
{
    private $request;
    private $response;
    private $apiMock;
    private $getAction;
    private $resultMock;
    private $orderRepository;

    protected function setUp(): void
    {
        $this->request = $this->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultMock = $this->getMockBuilder(ResultFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->setMethods(['setRedirect', 'representJson', 'setHttpResponseCode'])
            ->getMockForAbstractClass();

        $this->apiMock = $this->getMockBuilder(Api::class)
            ->setMethods(['initSdk', 'parseRequest'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $apiLog = $this->getMockBuilder(Log::class)
            ->setMethods(['log', 'logException'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $context = $this->getMockBuilder(Context::class)
            ->setMethods(['getRequest', 'getResponse', 'getResultFactory'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $context
            ->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $context
            ->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($this->response));

        $context
            ->expects($this->any())
            ->method('getResultFactory')
            ->will($this->returnValue($this->resultMock));

        $helper = $this->createMock(Order\Helper::class);
        $apiConfig = $this->createConfiguredMock(Config::class, ['isEnabled' => true]);
        $eventManager = $this->createMock(\Magento\Framework\Event\Manager::class);
        $orderLog = $this->createMock(\Riskified\Decider\Model\Api\Order\Log::class);
        $helperContext = $this->createConfiguredMock(
            \Magento\Framework\App\Helper\Context::class,
            ['getEventManager' => $eventManager]
        );
        $backendSession = $this->createMock(\Magento\Backend\Model\Auth\Session::class);
        $messageManager = $this->createMock(\Magento\Framework\Message\ManagerInterface::class);
        $date = $this->createMock(\Magento\Framework\Stdlib\DateTime\DateTime::class);
        $queueFactory = $this->createMock(\Riskified\Decider\Model\QueueFactory::class);

        $this->orderRepository = $this->getMockBuilder(\Magento\Sales\Model\OrderRepository::class)
            ->setMethods(['get'])
            ->disableOriginalConstructor()
            ->getMockForAbstractClass();

        $checkoutSession = $this->createMock(\Magento\Checkout\Model\Session::class);
        $sessionManager = $this->createMock(\Magento\Framework\Session\SessionManager::class);
        $searchCriteriaBuilder = $this->createMock(\Magento\Framework\Api\SearchCriteriaBuilder::class);

        $orderApiMock = new Order(
            $this->apiMock,
            $helper,
            $apiConfig,
            $orderLog,
            $helperContext,
            $backendSession,
            $messageManager,
            $date,
            $queueFactory,
            $this->orderRepository,
            $checkoutSession,
            $sessionManager,
            $searchCriteriaBuilder
        );

        $this->getAction = new Get(
            $context,
            $this->apiMock,
            $orderApiMock,
            $apiLog
        );
    }

    public function testExecute()
    {
        $jsonRequest = '{"status":"submitted", "order_id":1}';
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false
        );

        $this->resultMock
            ->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->will($this->returnValue($dataObject));

        $stdClass = new \stdClass();
        $stdClass->id = 0;
        $stdClass->status = "test";

        $this->apiMock
            ->expects($this->once())
            ->method('parseRequest')
            ->with($this->request)
            ->willReturn($stdClass);

        $this->getAction->execute();
    }

    public function testExecuteSubmittedStatus()
    {
        $jsonRequest = '{"status":"submitted", "order_id":1}';
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false
        );

        $this->resultMock
            ->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->will($this->returnValue($dataObject));

        $stdClass = new \stdClass();
        $stdClass->id = 1;
        $stdClass->status = "submitted";
        $stdClass->oldStatus = "new";
        $stdClass->description = "Under review by Riskified";

        $orderObject = $this->createPartialMock(
            SalesOrder::class,
            ['getId']
        );

        $this->orderRepository
            ->method('get')
            ->with(1)
            ->willReturn($orderObject);

        $orderObject
            ->method('getId')
            ->willReturn(1);

        $this->apiMock
            ->expects($this->once())
            ->method('parseRequest')
            ->with($this->request)
            ->willReturn($stdClass);

        $this->getAction->execute();
    }

    public function testExecuteApprovedStatus()
    {
        $jsonRequest = '{"status":"approved", "order_id":1}';
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false
        );

        $this->resultMock
            ->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->will($this->returnValue($dataObject));

        $stdClass = new \stdClass();
        $stdClass->id = 1;
        $stdClass->status = "approved";
        $stdClass->oldStatus = "submitted";
        $stdClass->description = "Approved By Riskified";

        $orderObject = $this->createPartialMock(
            SalesOrder::class,
            ['getId']
        );

        $this->orderRepository
            ->method('get')
            ->with(1)
            ->willReturn($orderObject);

        $orderObject
            ->method('getId')
            ->willReturn(1);

        $this->apiMock
            ->expects($this->once())
            ->method('parseRequest')
            ->with($this->request)
            ->willReturn($stdClass);

        $this->getAction->execute();
    }

    public function testExecuteRejectedStatus()
    {
        $jsonRequest = '{"status":"approved", "order_id":1}';
        $this->request->method('getContent')
            ->willReturn($jsonRequest);

        $this->request->method('getMethod')
            ->willReturn('POST');

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [],
            '',
            false
        );

        $this->resultMock
            ->expects($this->any())
            ->method('create')
            ->with(ResultFactory::TYPE_JSON)
            ->will($this->returnValue($dataObject));

        $stdClass = new \stdClass();
        $stdClass->id = 1;
        $stdClass->status = "approved";
        $stdClass->oldStatus = "submitted";
        $stdClass->description = "Approved By Riskified";

        $orderObject = $this->createPartialMock(
            SalesOrder::class,
            ['getId']
        );

        $this->orderRepository
            ->method('get')
            ->with(1)
            ->willReturn($orderObject);

        $orderObject
            ->method('getId')
            ->willReturn(1);

        $this->apiMock
            ->expects($this->once())
            ->method('parseRequest')
            ->with($this->request)
            ->willReturn($stdClass);

        $this->getAction->execute();
    }
}
