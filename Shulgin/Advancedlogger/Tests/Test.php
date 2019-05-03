<?php

namespace Shulgin\AdvancedLogger\Tests;
use Shulgin\AdvancedLogger\Logger\Logger;
use UnderstandMonolog\Handler\UnderstandAsyncHandler;
use Monolog\Handler\ChromePHPHandler;
use Shulgin\AdvancedLogger\Helper\Data;
use Shulgin\AdvancedLogger\Logger\Handler\UnderstandIoHandler;

/**
 * Class Shulgin\AdvancedLogger\Tests\Test
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class Test {
    protected $_logger;
    protected $_helper;

    public function __construct(
        Logger $logger,
        Data $helper
    ){
        $this->_logger = $logger;
        $this->_helper = $helper;
    }


    public function test()
    {

       // echo $this->_helper->UnderstandIoActive();
       // echo $this->_helper->UnderstandIoApiKey();
        //echo $this->_helper->UnderstandIoLogLevel();

       // die('sdf');
        //$x = new UnderstandIoHandler();
        //$y = new ChromePHPHandler();

        //var_dump($x);die;
        //$this->_logger->pushHandler($x);
        //$this->_logger->pushHandler($y);
        
        //var_dump($this->_logger);die;
        //$this->_logger->log('debug', '123', [1,2,3] , 'test.log'); //  , 'test.log', $path = null 
        $this->_logger->addError('123', [1,2,3]);       
    }
}