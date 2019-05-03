<?php

namespace Shulgin\AdvancedLogger\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Config\ScopeConfigInterface;

/*
* Class Shulgin\AdvancedLogger\Helper\Data 
* hendles system config configuratin .
*/
class Data extends AbstractHelper
{
    /** @var Magento\Framework\App\Config\ScopeConfigInterface **/
    protected $scopeConfig;

    const UNDERSTAND_ACTIVE = 'logger/understandio/active';
    const UNDERSTAND_API_KEY = 'logger/understandio/api_key';
    const UNDERSTAND_LOG_LEVEL = 'logger/understandio/log_level';
    const CHROMEPHPHANDLER_ACTIVE = 'logger/chromephphandler/active';
    const CHROMEPHPHANDLER_LOG_LEVEL = 'logger/chromephphandler/log_level';


    /**
         * @param \Magento\Framework\App\Helper\Context $context
         */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context);
    }

    /**
         * @return bool
         */
    public function isEnabled()
    {
        return true;
    }
    
    /**
        *  Gets UnderstandIo active config.
        *  @return bool
        */
    public function UnderstandIoActive()
    {

        $active = $this->getConfig(self::UNDERSTAND_ACTIVE);
        $apiKey = $this->UnderstandIoApiKey();

        return $active && $apiKey !== '';
    }

    /**
         *  Gets UnderstandIo api key config.
         *  @return string
         */
    public function UnderstandIoApiKey()
    {
        return $this->getConfig(self::UNDERSTAND_API_KEY);
    }

    /**
         * Gets UnderstandIo log level config.
         *  @param $returnString
         *  @return  $config | string | array 
         */
    public function UnderstandIoLogLevel($returnString = true)
    {
        $config = $this->getConfig(self::UNDERSTAND_LOG_LEVEL);
        if ($returnString) {
            return $config;
        } else {
            return explode(',', $config);
        } 
    }

    /**
        *  Gets Chrome log active config.
        *  @return bool
        */
    public function chromephphandlerActive()
    {
        $this->getConfig(self::CHROMEPHPHANDLER_ACTIVE);
    }

    /**
         * Gets Chrome log level config.
         *  @param $returnString
         *  @return  $config | string | array 
         */
    public function chromephphandlerLogLevel()
    {
        $config = $this->getConfig(self::CHROMEPHPHANDLER_LOG_LEVEL);
        if ($returnString) {
            return $config;
        } else {
            return explode(',', $config);
        } 
    }

    /**
         *  Gets store config value .
         *  @param $config | config path string
         */
    private function getConfig($config)
    {
        return $this->scopeConfig->getValue($config, \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
    }
    
}