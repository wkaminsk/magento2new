<?php
namespace Riskified\Decider\Test\Unit\Model\Api;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use Magento\Sales\Model\Order;
use PHPUnit\Framework\TestCase;
use Riskified\Common\Riskified;
use Riskified\Common\Validations;
use Riskified\Decider\Model\Api\Config;

class ApiTest extends TestCase
{
    private $config;
    public function setUp()
    {
        $om = new ObjectManager($this);
        $this->config = $this->createPartialMock(Config::class, ['getAuthToken', 'getConfigEnv', 'getShopDomain']);

        $this->config
            ->expects($this->once())
            ->method('getAuthToken')
            ->willReturn('098f6bcd4621d373cade4e832627b4f6');

        $this->config
            ->expects($this->once())
            ->method('getConfigEnv')
            ->willReturn('sandbox');

        $this->config
            ->expects($this->once())
            ->method('getShopDomain')
            ->willReturn('demo.com');
    }

    public function testInitSdk()
    {
        $order = $this->createPartialMock(Order::class, ['getStore']);

        $store = $this->getMockForAbstractClass(
            AbstractModel::class,
            [
                'dataHasChangedFor' => true,
                'getId' => 1,
            ],
            '',
            false
        );
        $order
            ->expects($this->once())
            ->method('getStore')
            ->willReturn($store);

        $this->config->setStore($order->getStore());

        $authToken = $this->config->getAuthToken();
        $env = $this->config->getConfigEnv();
        $shopDomain = $this->config->getShopDomain();

        Riskified::init($shopDomain, $authToken, $env, Validations::SKIP);
    }
}
