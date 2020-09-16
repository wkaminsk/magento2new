<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Log as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Observer\SalesOrderShipmentSaveAfter;

class SalesOrderShipmentSaveAfterTest extends TestCase
{
    /** @var object SalesOrderShipmentSaveAfter */
    protected $object;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $logger = $this->createMock(LogApi::class);
        $api = $this->createMock(OrderApi::class);

        $this->object = $this->objectManager->getObject(
            SalesOrderShipmentSaveAfter::class,
            [
                'logger' => $logger,
                'api' => $api
            ]
        );
    }
    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getShipment']);

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
//                'getId' => 1,
            ],
            '',
            false
        );

        $observer
            ->expects($this->once())
            ->method('getShipment')
            ->willReturn(
                $dataObject
            );

        $this->object->execute($observer);
    }
}
