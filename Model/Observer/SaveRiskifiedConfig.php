<?php

namespace Riskified\Decider\Model\Observer;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\UrlInterface;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Store\Model\StoreManagerInterface;
use Riskified\Decider\Model\Api\Config as ApiConfig;
use Riskified\Decider\Model\Api\Log;
use Riskified\Decider\Model\Api\Merchant as ApiMerchant;
use Riskified\OrderWebhook\Exception\CurlException;
use Riskified\OrderWebhook\Exception\UnsuccessfulActionException;
use Riskified\OrderWebhook\Model;

class SaveRiskifiedConfig implements ObserverInterface
{
    /**
     * @var Log
     */
    private $logger;

    /**
     * @var ApiMerchant
     */
    private $apiMerchantLayer;

    /**
     * @var ApiConfig
     */
    private $apiConfig;

    /**
     * @var PaymentConfig
     */
    private $_paymentConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $storeConfig;

    /**
     * @param Log $logger
     * @param ApiConfig $config
     * @param PaymentConfig $paymentConfig
     * @param ApiMerchant $merchantApi
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $storeConfig
     */
    public function __construct(
        Log $logger,
        ApiConfig $config,
        PaymentConfig $paymentConfig,
        ApiMerchant $merchantApi,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $storeConfig
    ) {
        $this->logger = $logger;
        $this->apiConfig = $config;
        $this->_paymentConfig = $paymentConfig;
        $this->apiMerchantLayer = $merchantApi;
        $this->storeManager = $storeManager;
        $this->storeConfig = $storeConfig;
    }

    /**
     * @param Observer $observer
     * @throws NoSuchEntityException
     * @throws CurlException
     * @throws UnsuccessfulActionException
     */
    public function execute(Observer $observer)
    {
        $this->logger->log(__("Running save riskified config"), 2);
        if (!$this->apiConfig->isEnabled()) {
            return;
        }

        $helper = $this->apiConfig;
        $allActiveMethods = $this->_paymentConfig->getActiveMethods();
        $settings = $this->storeConfig->getValue('riskified/riskified');

        $gateWays = '';

        foreach ($allActiveMethods as $key => $value) {
            $gateWays .= $key . ",";
        }

        $extensionVersion = $helper->getExtensionVersion();

        $shopHostUrl = $this->storeManager->getStore()
            ->getBaseUrl(UrlInterface::URL_TYPE_WEB);

        $settings['gws'] = $gateWays;
        $settings['host_url'] = $shopHostUrl;
        $settings['extension_version'] = $extensionVersion;
        unset($settings['key']);
        unset($settings['domain']);

        $settingsModel = new Model\MerchantSettings([
            'settings' => $settings
        ]);

        if ($this->apiConfig->getAuthToken() && $this->apiConfig->getShopDomain()) {
            $this->apiMerchantLayer->update($settingsModel);
        }
    }
}
