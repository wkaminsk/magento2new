<?php

namespace Riskified\Decider\Test\Unit\Model\Config\Source;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Riskified\Decider\Model\Config\Source\Env;

class EnvTest extends \PHPUnit\Framework\TestCase
{
    private $env;

    public function setUp()
    {
        $om = new ObjectManager($this);
        $this->env = $om->getObject(Env::class);
    }
    /**
     * Options getter
     *
     * @return array
     */
    public function testToOptionArray()
    {
        $retValue = [
            ['value' => 'PROD', 'label' => __('Production')],
            ['value' => 'SANDBOX', 'label' => __('Sandbox')],
            ['value' => 'DEV', 'label' => __('Dev')]
        ];

        $this->assertEquals($retValue, $this->env->toOptionArray());
    }
}
