<?php

namespace Riskified\Decider\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Riskified\Decider\Model\Config\Source\ApprovedState;

class ApprovedStateTest extends \PHPUnit\Framework\TestCase
{
    private $approvedState;

    public function setUp()
    {
        $om = new ObjectManager($this);
        $this->approvedState = $om->getObject(ApprovedState::class);
    }

    public function testToOptionArray()
    {
        $retValue = [
            [
                'value' => "processing",
                'label' => __("processing")
            ],
            [
                'value' => "holded",
                'label' => __("holded")
            ]
        ];

        $this->assertEquals($retValue, $this->approvedState->toOptionArray());
    }
}
