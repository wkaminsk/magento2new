<?php

namespace Riskified\Decider\Test\Unit\Model\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event\Observer;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Riskified\Decider\Model\Observer\CollectPaymentInfo;
use PHPUnit\Framework\TestCase;

class CollectPaymentInfoTest extends TestCase
{
    private $objectManager;
    private $object;

    public function setUp()
    {
        $this->objectManager = new ObjectManager($this);
        $this->object = $this->objectManager->getObject(
            CollectPaymentInfo::class
        );
    }

    public function testExecute()
    {
        $observer = $this->createPartialMock(Observer::class, ['getQuote']);

        $observer
            ->expects($this->once())
            ->method('getQuote')
            ->willReturn(
                new DataObject(['payment' => new DataObject(['cc_number' => "4111111111111111"])])
            );

        $this->object->execute($observer);
    }
}
