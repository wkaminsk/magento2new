<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Observer\OrderPaymentRefund;

class OrderPaymentRefundTest extends TestCase
{
    private $object;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $logger = $this->createMock(LogApi::class);
        $api = $this->createMock(OrderApi::class);

        $this->object = $objectManager->getObject(
            OrderPaymentRefund::class,
            [
                'log' => $logger,
                'api' => $api
            ]
        );
    }

    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getEvent']);

        $observer
            ->expects($this->once())
            ->method('getEvent')
            ->willReturn(
                new DataObject(['creditmemo' => new DataObject(['id' => "1"])])
            );

        $this->object->execute($observer);
    }
}
