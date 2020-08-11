<?php
namespace Riskified\Decider\Test\Unit\Block;
use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class JSTest
 */
class JsTest extends \PHPUnit\Framework\TestCase
{
    private $apiConfigMock;
    private $sessionMock;
    private $apiConfigModel;
    private $block;

    protected function setUp()
    {
        $this->sessionMock = $this->createMock(\Magento\Framework\Session\SessionManager::class);
        $this->apiConfigMock = $this->createMock(\Riskified\Decider\Model\Api\Config::class);
        $context = $this->createMock(\Magento\Framework\View\Element\Template\Context::class);

        $objectManager = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $scopeConfigInterface = $this->createMock(\Magento\Framework\App\Config\ScopeConfigInterface::class);
        $cookieManagerInterface = $this->createMock(\Magento\Framework\Stdlib\CookieManagerInterface::class);
        $fullModuleList = $this->createMock(\Magento\Framework\Module\FullModuleList::class);
        $session = $this->createMock(\Magento\Checkout\Model\Session::class);

        $this->apiConfigModel = $objectManager->getObject(
            \Riskified\Decider\Model\Api\Config::class,
            [
                'ScopeConfigInterface' => $scopeConfigInterface,
                'CookieManagerInterface' => $cookieManagerInterface,
                'FullModuleList' => $fullModuleList,
                'Session' => $session
            ]
        );


        $this->block = $objectManager->getObject(
            \Riskified\Decider\Block\Js::class,
            [
                'Context' => $context,
                'Config' => $this->apiConfigMock,
                'data' => []
            ]
        );

//        var_dump($this->block->getConfigBeaconUrl());
    }

    public function testIsEnabled()
    {
        $this->assertEquals($this->apiConfigModel->isEnabled(), $this->block->isEnabled());
    }

    /**
     * @return mixed
     */
    public function testGetShopDomain()
    {
        $this->assertEquals($this->apiConfigModel->getShopDomain(), $this->block->getShopDomain());
    }

    /**
     * @return bool|mixed
     */
    public function testGetConfigStatusControlActive()
    {
        $this->assertEquals($this->apiConfigModel->getConfigStatusControlActive(), $this->block->getConfigStatusControlActive());
    }

    /**
     * @return mixed|string
     */
    public function testGetExtensionVersion()
    {
        $this->assertEquals($this->apiConfigModel->getExtensionVersion(), $this->block->getExtensionVersion());
    }
}
