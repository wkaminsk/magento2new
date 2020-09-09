<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use Riskified\Decider\Model\Observer\OrderPlacedAfter;

class OrderPlacedAfterTest extends TestCase
{
    /** @var OrderPlacedAfter */
    protected $object;

    /** @var ObjectManager */
    protected $objectManager;
    protected $orderFactory;
    protected $logger;
    protected $api;
    protected $dataObject;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);

        $this->logger = $this->createMock(OrderLogger::class);
        $this->api = $this->createMock(OrderApi::class);

        $this->object = $this->objectManager->getObject(
            OrderPlacedAfter::class,
            [
                'logger' => $this->logger,
                'api' => $this->api
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
