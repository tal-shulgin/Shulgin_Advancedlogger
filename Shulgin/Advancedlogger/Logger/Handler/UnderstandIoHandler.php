<?php

namespace Shulgin\AdvancedLogger\Logger\Handler;
use Shulgin\AdvancedLogger\Helper\Data;
use Monolog\Logger;

/**
 * 
 * Class Shulgin\AdvancedLogger\Logger\Formatter\MultiLineFormatter
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class UnderstandIoHandler extends \UnderstandMonolog\Handler\UnderstandAsyncHandler
{
    protected $_helper;

    public function __construct()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $objectManager->create('Shulgin\AdvancedLogger\Helper\Data');
        
        if ($this->_helper->UnderstandIoActive()) {
             parent::__construct($this->_helper->UnderstandIoApiKey(), $apiUrl = 'https://api.understand.io', $silent = true, $sslBundlePath = false, $level = Logger::DEBUG, $bubble = true);
        }
    }

    public function isHandling(array $record)
    {
        if(!$this->_helper->UnderstandIoActive()) {
            return false;
        } else {
           return in_array($record['level'], $this->_helper->UnderstandIoLogLevel(false));
        }
    }
}
?>