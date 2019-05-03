<?php

namespace Shulgin\AdvancedLogger\Logger\Handler;
//use Monolog\Formatter\LineFormatter;

/**
 * 
 * Class Shulgin\AdvancedLogger\Logger\Formatter\MultiLineFormatter
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class StreamHandler extends \Monolog\Handler\StreamHandler
{
    /**
     * {@inheritdoc}
     */
    public function handle(array $record)
    {
        if (!$this->isHandling($record)) {
            return false;
        }

        $record = $this->processRecord($record);

        $record['formatted'] = $this->getFormatter()->format($record);
        $this->write($record);

        return false === $this->bubble;
    }

    /**
     * Gets the default formatter.
     *
     * @return \Shulgin\AdvancedLogger\Logger\Formatter\MultiLineFormatter
     */
    protected function getDefaultFormatter()
    {
        //return new LineFormatter('%channel%.%level_name%: %message% %context% %extra%');
        return new \Shulgin\AdvancedLogger\Logger\Formatter\MultiLineFormatter(null, null, true, true);
    }
}