<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Api\Log as OrderLogger;
use Riskified\Decider\Model\Observer\UpdateOrderState;

class UpdateOrderStateTest extends TestCase
{
    /** @var UpdateOrderState */
    private $object;
    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $logger = $this->createMock(OrderLogger::class);
        $api = $this->createMock(OrderApi::class);

        $this->object = $objectManager->getObject(
            UpdateOrderState::class,
            [
                'logger' => $logger,
                'api' => $api
            ]
        );
    }

    public function testExecute()
    {
        /*$observer = $this->createPartialMock(Observer::class, ['getOrder', 'getStatus', 'getOldStatus', 'getDescription']);

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'dataHasChangedFor' => true,
                'getId' => 1,
            ],
            '',
            false,
            true,
            true,
            ['addStatusHistoryComment', 'save']
        );

        $observer
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn(
                $dataObject
            );

        $observer
            ->expects($this->once())
            ->method('getStatus')
            ->willReturn(
                'approved'
            );

        $observer
            ->expects($this->once())
            ->method('getOldStatus')
            ->willReturn(
                'submitted'
            );

        $observer
            ->expects($this->once())
            ->method('getDescription')
            ->willReturn(
                'Order was approved by Riskified'
            );

        $this->object->execute($observer);*/
    }
}
