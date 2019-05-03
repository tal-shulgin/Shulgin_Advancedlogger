<?php

namespace Shulgin\AdvancedLogger\Logger\Handler;

use Monolog\Handler\ChromePHPHandler;
use Shulgin\AdvancedLogger\Helper\Data;
use Monolog\Logger;

/**
 * Class Shulgin\AdvancedLogger\Logger\Handler\ChromeLogandler
 *  
 */
class ChromeLogandler extends ChromePHPHandler
{
    /** @var  Shulgin\AdvancedLogger\Helper\Data */
    protected $_helper;

    public function __construct()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_helper = $objectManager->create('Shulgin\AdvancedLogger\Helper\Data');
        
        if ($this->_helper->chromephphandlerActive()) {
             parent::__construct($level = Logger::DEBUG, $bubble = true);
        }
    }

    public function isHandling(array $record)
    {
        if(!$this->_helper->chromephphandlerActive()) {
            return false;
        } else {
           return in_array($record['level'], $this->_helper->chromephphandlerLogLevel(false));
        }
    }
}
?>