<?php

namespace Shulgin\AdvancedLogger\Logger;

/**
 * 
 * Class Shulgin\AdvancedLogger\Logger\Handler
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;
 
    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/customfile.log';

}