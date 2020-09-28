<?php

namespace Riskified\Decider\Test\Unit\Model\Api;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;
use Riskified\Decider\Model\Api\Api;
use Riskified\Decider\Model\Api\Config;
use Riskified\Decider\Model\Api\Merchant;
use Riskified\Decider\Model\Api\Order\Helper;
use Riskified\Decider\Model\Logger\Merchant as MerchantLogger;
use Riskified\OrderWebhook\Model\MerchantSettings;
use Riskified\OrderWebhook\Transport\CurlTransport;

class MerchantTest extends TestCase
{
    /** @var Merchant */
    private $merchantModel;

    public function setUp()
    {
        $om = new ObjectManager($this);

        $api = $this->createMock(Api::class);
        $helper = $this->createMock(Helper::class);
        $config = $this->createMock(Config::class);
        $logger = $this->createMock(MerchantLogger::class);
        $context = $this->createMock(Context::class);
        $manager = $this->createMock(ManagerInterface::class);
        $transport = $this->createPartialMock(CurlTransport::class, ['updateMerchantSettings']);

        $api->method('getTransport')->willReturn($transport);

        $this->merchantModel = $om->getObject(
            Merchant::class,
            [
                'log' => $logger,
                'api' => $api,
                'helper' => $helper,
                'config' => $config,
                'context' => $context,
                'manager' => $manager,
            ]
        );
    }

    public function testUpdate()
    {
        $exceptedResult = new \stdClass();
        $exceptedResult->status = 200;
        $exceptedResult->description = "";

        $settings = [];
        $settings['gws'] = "Braintree,Authorizenet";
        $settings['host_url'] = "https://domain.com";
        $settings['extension_version'] = "1.1.0";

        $merchantMock = $this->createMock(MerchantSettings::class);
        $merchantMock->settings = $settings;

        $result = $this->merchantModel->update($merchantMock);

//        $this->assertEquals($exceptedResult, $result);
    }
}
