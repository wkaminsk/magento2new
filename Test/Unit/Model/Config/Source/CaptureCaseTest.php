<?php

namespace Riskified\Decider\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Riskified\Decider\Model\Config\Source\CaptureCase;

class CaptureCaseTest extends \PHPUnit\Framework\TestCase
{
    private $captureCase;

    public function setUp()
    {
        $om = new ObjectManager($this);
        $this->captureCase = $om->getObject(CaptureCase::class);
    }

    public function testToOptionArray()
    {
        $retValue = [
            ['value' => "online", 'label' => __('Capture Online')],
            ['value' => "offline", 'label' => __('Capture Offline')]
        ];

        $this->assertEquals($retValue, $this->captureCase->toOptionArray());
    }
}
