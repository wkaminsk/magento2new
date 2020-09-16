<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Logger\Order as LogApi;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Observer\OrderPlacedAfter;

class OrderPlacedAfterTest extends TestCase
{
    /** @var OrderPlacedAfter */
    protected $object;

    public function setUp()
    {
        $objectManager = new ObjectManager($this);

        $logger = $this->createMock(LogApi::class);
        $api = $this->createMock(OrderApi::class);

        $this->object = $objectManager->getObject(
            OrderPlacedAfter::class,
            [
                'logger' => $logger,
                'api' => $api
            ]
        );
    }

    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder']);

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'dataHasChangedFor' => true,
                'getId' => 1,
            ],
            '',
            false
        );

        $observer
            ->expects($this->once())
            ->method('getOrder')
            ->willReturn(
                $dataObject
            );

        $this->object->execute($observer);
    }
}
