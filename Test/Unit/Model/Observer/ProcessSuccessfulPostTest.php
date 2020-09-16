<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Logger\Order as OrderLogger;
use Riskified\Decider\Model\Api\Order as OrderApi;
use Riskified\Decider\Model\Observer\ProcessSuccessfulPost;

class ProcessSuccessfulPostTest  extends TestCase
{
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
            ProcessSuccessfulPost::class,
            [
                'logger' => $this->logger,
                'api' => $this->api
            ]
        );
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getOrder', 'getResponse']);

        $dataObject = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'dataHasChangedFor' => true,
                'getId' => 1,
            ],
            '',
            false
        );

        $order = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'status' => 'approved',
                'old_status' => 'pending',
                'description' => 'Order was approved by order',
                'getId' => 1,
            ],
            '',
            false
        );

        $response = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'order' => $order
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

        $observer
            ->expects($this->once())
            ->method('getResponse')
            ->willReturn(
                $response
            );

        $this->object->execute($observer);
    }
}
