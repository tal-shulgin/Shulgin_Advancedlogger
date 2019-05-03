<?php

namespace Shulgin\AdvancedLogger\Model\Config\Source;
use Monolog\Logger;

class LogLevel implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => Logger::DEBUG,     'label' => __('DEBUG')],
            ['value' => Logger::INFO,      'label' => __('INFO')],
            ['value' => Logger::NOTICE,    'label' => __('NOTICE')],
            ['value' => Logger::WARNING,   'label' => __('WARNING')],
            ['value' => Logger::ERROR,     'label' => __('ERROR')],
            ['value' => Logger::CRITICAL,  'label' => __('CRITICAL')],
            ['value' => Logger::ALERT,     'label' => __('ALERT')],
            ['value' => Logger::EMERGENCY, 'label' => __('EMERGENCY')]
        ];
    }

    public function toArray()
    {
        return [
            Logger::DEBUG     => __('DEBUG'),
            Logger::INFO      => __('INFO'),
            Logger::NOTICE    => __('NOTICE'),
            Logger::WARNING   => __('WARNING'),
            Logger::ERROR     => __('ERROR'),
            Logger::CRITICAL  => __('CRITICAL'),
            Logger::ALERT     => __('ALERT'),
            Logger::EMERGENCY => __('EMERGENCY')
        ];
    }
}
