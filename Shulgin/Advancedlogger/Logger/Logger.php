<?php

namespace Shulgin\AdvancedLogger\Logger;

use Magento\Setup\Exception;
use Shulgin\AdvancedLogger\Logger\Handler\StreamHandler;
use Shulgin\AdvancedLogger\Logger\Handler\UnderstandIoHandler;
use Monolog\Handler\ChromePHPHandler;

/**
 * 
 * Class Shulgin\AdvancedLogger\Logger\AcmeLogger
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class Logger extends \Monolog\Logger
{

    /** Default log base directory path */
    const DEFAULT_BASE_PATH = '/var/log/';
    private $trace;

    /**
         * Logger constructor.
         * @param $name
         * @param array $handlers
         * @param array $processors
         */
    public function __construct($name, array $handlers = [], array $processors = [])
    {
        $this->name = $name;
        $this->setHandlers($handlers);
        $this->pushHandler(new UnderstandIoHandler());
        $this->pushHandler(new ChromePHPHandler());
        $this->processors = $processors;
    }

    /**
         * Adds log record to file.
         *
         * @param int $level
         * @param string $message
         * @param array $context
         * @param array $extra
         * @param string $stream
         * @return bool
         * @throws \Exception
         */
    public function addRecord($level, $message, array $context = [], array $extra = [], $stream = '')
    {
        if($message instanceof \Exception) {
            $context['is_exception'] = $message instanceof \Exception;
        }

        if (!$this->handlers && empty($stream)) {
            try{
                $this->pushHandler(new StreamHandler('php://stderr', static::DEBUG, true, 0666));
            } catch (Exception $e) {
                parent::critical($e);
            }
        }

        $levelName = static::getLevelName($level);

        // check if any handler will handle this message so we can return early and save cycles
        $handlerKey = null;
        reset($this->handlers);
        while ($handler = current($this->handlers)) {
            if ($handler->isHandling(array('level' => $level))) {
                $handlerKey = key($this->handlers);
                break;
            }

            next($this->handlers);
        }

        if (null === $handlerKey) {
            return false;
        }

        if (!static::$timezone) {
            static::$timezone = new \DateTimeZone(date_default_timezone_get() ?: 'UTC');
        }

        // php7.1+ always has microseconds enabled, so we do not need this hack
        if ($this->microsecondTimestamps && PHP_VERSION_ID < 70100) {
            $ts = \DateTime::createFromFormat('U.u', sprintf('%.6F', microtime(true)), static::$timezone);
        } else {
            $ts = new \DateTime(null, static::$timezone);
        }
        $ts->setTimezone(static::$timezone);

        $record = [
            'message' => (string) $message,
            'context' => $context,
            'level' => $level,
            'level_name' => $levelName,
            'channel' => $this->name,
            'datetime' => $ts,
            'extra' => $extra,
        ];

        foreach ($this->processors as $processor) {
            $record = call_user_func($processor, $record);
        }

        //var_dump($stream);die;
        if (!empty($stream)) {
            try {
                $handler = new StreamHandler($stream, $level, true, 0666);
                $handler->handle($record);
            } catch (Exception $e) {
                parent::critical($e);
            }
        } 

        while ($handler = current($this->handlers)) {
            if (true === $handler->handle($record)) {
                break;
            }

            next($this->handlers);
        }
    

        return true;
    }

    /**
         * Add a log record
         *
         * @param int|string $level
         * @param mixed $message
         * @param array $context
         * @param string $file
         * @param array|string $path
         * @return bool
         */
    public function log($level, $message, array $context = [], $file = '', $path = null)
    {
        $this->initTrace();
        $level = static::toMonologLevel($level);
        
        if (gettype($message) == 'array' || gettype($message) == 'object') {
            $message = print_r($message, true);
        } 

        $stream = $this->getPath($path, true) . (!empty($file) ? $file : $this->getLogFileName());
        $extra  = $this->getExtra();

        return $this->addRecord($level, $message, $context, $extra, $stream);
    }

    /**
         * Gets last element off debug_backtrace for use.
         */
    private function initTrace()
    {
        $trace =  debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        if(isset($trace[2]) && $trace[2]['function']){
            if(in_array($trace[2]['function'], $this->getLogCallerFunctions())){
                $this->trace = $trace[3];
            } else {
                $this->trace = $trace[2];
            }
        } else {
            $this->trace = end($trace);
        }
        unset($trace);
    }

    /**
         * Gets array of functions call log function ,
         * use for filter back trade.
         *
         * @return array
         */
    private function getLogCallerFunctions()
    {
        return ['addDebug','addInfo','addNotice','addWarning','addError','addCritical','addAlert','addEmergency',
            'debug','info','notice','warning','error','critical','alert','emergency',
            ];
    }

    /**
         *  Gets trace
         */
    private function getTrace()
    {
        return $this->trace;
    }

    /**
         * Adds data to logs context.
         */
    private function getExtra()
    {
        $trace = $this->getTrace();
        unset($trace['file']);
        unset($trace['type']);
        return $trace;
    }

    /**
         * Sets log file name, base on caller class name. use debug_backtrace.
         * @return string $fileName
         */
    private function getLogFileName()
    {
        $trace = $this->getTrace();

        if(isset($trace['class'])) {
            $names = explode('\\', $trace['class']);
        } else {
            $names = explode('\\', __NAMESPACE__);
        }

        return $names[0] ."_". $names[1] .".log";
    } 

    /**
         * Get log directory/file full path
         *
         * @param array|string $path
         * @param bool $isRealPath
         * @return string
         */
    public function getPath($path = null, $isRealPath = false)
    {
        $rootPath = '';
        if ($isRealPath) {
            $rootPath = BP;
        }

        if (gettype($path) == 'array') {
            $path = implode('', $path);
        }

        return $rootPath . static::DEFAULT_BASE_PATH . $path;
    }

    /**
         * Adds a log record at the DEBUG level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addDebug($message, array $context = array())
    {
        return $this->log(static::DEBUG, $message, $context);
    }

    /**
         * Adds a log record at the INFO level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addInfo($message, array $context = array())
    {
        return $this->log(static::INFO, $message, $context);
    }

    /**
         * Adds a log record at the NOTICE level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addNotice($message, array $context = array())
    {
        return $this->log(static::NOTICE, $message, $context);
    }

    /**
         * Adds a log record at the WARNING level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addWarning($message, array $context = array())
    {
        return $this->log(static::WARNING, $message, $context);
    }

    /**
         * Adds a log record at the ERROR level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addError($message, array $context = array())
    {
        return $this->log(static::ERROR, $message, $context);
    }

    /**
         * Adds a log record at the CRITICAL level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addCritical($message, array $context = array())
    {
        return $this->log(static::CRITICAL, $message, $context);
    }

    /**
         * Adds a log record at the ALERT level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addAlert($message, array $context = array())
    {
        return $this->log(static::ALERT, $message, $context);
    }

    /**
         * Adds a log record at the EMERGENCY level.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function addEmergency($message, array $context = array())
    {
        return $this->log(static::EMERGENCY, $message, $context);
    }

    /**
         * Adds a log record at the DEBUG level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function debug($message, array $context = array())
    {
        return $this->log(static::DEBUG, $message, $context);
    }

    /**
         * Adds a log record at the INFO level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function info($message, array $context = array())
    {
        return $this->log(static::INFO, $message, $context);
    }

    /**
         * Adds a log record at the NOTICE level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function notice($message, array $context = array())
    {
        return $this->log(static::NOTICE, $message, $context);
    }

    /**
         * Adds a log record at the WARNING level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function warn($message, array $context = array())
    {
        return $this->log(static::WARNING, $message, $context);
    }

    /**
         * Adds a log record at the WARNING level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function warning($message, array $context = array())
    {
        return $this->log(static::WARNING, $message, $context);
    }

    /**
         * Adds a log record at the ERROR level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function err($message, array $context = array())
    {
        return $this->log(static::ERROR, $message, $context);
    }

    /**
         * Adds a log record at the ERROR level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function error($message, array $context = array())
    {
        return $this->log(static::ERROR, $message, $context);
    }

    /**
         * Adds a log record at the CRITICAL level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function crit($message, array $context = array())
    {
        return $this->log(static::CRITICAL, $message, $context);
    }

    /**
         * Adds a log record at the CRITICAL level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function critical($message, array $context = array())
    {
        return $this->log(static::CRITICAL, $message, $context);
    }

    /**
         * Adds a log record at the ALERT level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function alert($message, array $context = array())
    {
        return $this->log(static::ALERT, $message, $context);
    }

    /**
         * Adds a log record at the EMERGENCY level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function emerg($message, array $context = array())
    {
        return $this->log(static::EMERGENCY, $message, $context);
    }

    /**
         * Adds a log record at the EMERGENCY level.
         *
         * This method allows for compatibility with common interfaces.
         *
         * @param  string  $message The log message
         * @param  array   $context The log context
         * @return Boolean Whether the record has been processed
         */
    public function emergency($message, array $context = array())
    {
        return $this->log(static::EMERGENCY, $message, $context);
    }

}
