<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use Riskified\Decider\Model\Observer\SalesOrderAddressUpdate;

class SalesOrderAddressUpdateTest extends TestCase
{
    protected $object;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $logger = $this->createMock(OrderLogger::class);
        $api = $this->createMock(OrderApi::class);

        $this->object = $objectManager->getObject(
            SalesOrderAddressUpdate::class,
            [
                'logger' => $logger,
                'api' => $api
            ]
        );
    }

    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrderId']);

        $observer
            ->expects($this->once())
            ->method('getOrderId')
            ->willReturn(
                1
            );

        $this->object->execute($observer);
    }
}
