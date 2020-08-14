<?php

namespace Riskified\Decider\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;

class HoldedStateStatusesTest extends \PHPUnit\Framework\TestCase
{
    private $configFactoryMock;
    private $configMock;
    private $model;

    public function setUp()
    {
        $this->configFactoryMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\ConfigFactory::class)
            ->setMethods(['create'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->configMock = $this
            ->getMockBuilder(\Magento\Sales\Model\Order\Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->configFactoryMock->expects($this->any())->method('create')->will(
            $this->returnValue($this->configMock)
        );

        $helper = new ObjectManager($this);
        $this->model = $helper->getObject(
            \Riskified\Decider\Model\Config\Source\HoldedStateStatuses::class,
            ['configFactory' => $this->configFactoryMock]
        );
    }

    public function testToOptionArray()
    {
        $except = [
            ['label' => __('Hold'), 'value' => 'holded'],
        ];
        $this->configMock
            ->expects($this->any())
            ->method('getStateStatuses')
            ->with(\Magento\Sales\Model\Order::STATE_HOLDED)
            ->will(
                $this->returnValue(
                    ['holded' => __("Hold")]
                )
            );

        $this->assertEquals($except, $this->model->toOptionArray());
    }
}
