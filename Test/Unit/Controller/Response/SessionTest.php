<?php

namespace Riskified\Decider\Test\Unit\Controller\Response;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class SessionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSessionMock;
    private $resultMock;
    private $resultFactoryMock;

    protected function setUp()
    {
        $this->resultMock = $this->getMockBuilder(ResultInterface::class)
            ->setMethods(['setHttpResponseCode', 'renderResult', 'setHeader', 'setData'])
            ->getMock();

        $this->resultFactoryMock = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();

        $this->resultFactoryMock->expects(static::once())
            ->method('create')
            ->willReturn($this->resultMock);

        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->customerSessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['getSessionId'])
            ->getMock();

        $context = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $context->expects(static::any())
            ->method('getRequest')
            ->willReturn($this->requestMock);
    }

    public function testExecute()
    {
        $sessionId = $this->customerSessionMock->expects($this->once())
            ->method('getSessionId')
            ->will($this->returnValue(1));

        $payload = ['session_id' => $sessionId];

        $this->resultFactoryMock
            ->method("create")
            ->with(ResultFactory::TYPE_JSON);

        $result = $this->resultFactoryMock
            ->method("setData")
            ->with($payload);

        return $result;
    }
}
