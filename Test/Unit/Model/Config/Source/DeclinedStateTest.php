<?php

namespace Riskified\Decider\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Config\Source\DeclinedState;

class DeclinedStateTest extends TestCase
{
    private $declinedState;

    public function setUp()
    {
        $om = new ObjectManager($this);
        $this->declinedState = $om->getObject(DeclinedState::class);
    }

    public function testToOptionArray()
    {
        $retValue = [
            ['value' => "canceled", 'label' => __('canceled')],
            ['value' => "holded", 'label' => __('holded')]
        ];

        $this->assertEquals($retValue, $this->declinedState->toOptionArray());
    }
}
