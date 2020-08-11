<?php

namespace Riskified\Decider\Test\Unit\Controller\Response;

use Magento\Framework\App\RequestInterface;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;

class BinTest extends \PHPUnit\Framework\TestCase
{
    private $requestMock;
    private $sessionMock;

    protected function setUp()
    {
        $this->requestMock = $this->getMockBuilder(RequestInterface::class)
            ->disableOriginalConstructor()
            ->setMethods(['getParam'])
            ->getMockForAbstractClass();

        $this->sessionMock = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->setMethods(['setRiskifiedBin'])
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
        $card_no = "4111111";

        $this
            ->requestMock
            ->expects($this->once())
            ->method('getParam')
            ->with('card')
            ->willReturn($card_no);

        $card_no = $this->requestMock->getParam('card');

        $this
            ->sessionMock
            ->expects($this->once())
            ->method('setRiskifiedBin')
            ->with($card_no);

        $this->sessionMock->setRiskifiedBin($card_no);
    }
}
