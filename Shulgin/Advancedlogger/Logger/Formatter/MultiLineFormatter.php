<?php

namespace Shulgin\AdvancedLogger\Logger\Formatter;

/**
 * Formats incoming records into a multi-line string | like print_r()
 * This is especially useful for logging to files
 * 
 * Class Shulgin\AdvancedLogger\Logger\Formatter\MultiLineFormatter
 * @package Shulgin\AdvancedLogger
 * @author Shulgin Tal <shulgin23@gmail.com>
 */
class MultiLineFormatter extends \Monolog\Formatter\NormalizerFormatter
{
    //const SIMPLE_FORMAT = "[%datetime%] %channel%.%level_name%: %message% %context% %extra%\n";
    const SIMPLE_FORMAT = "[%datetime%] %level_name%: %message%\n %extra%\n %context%\n\r";

    protected $format;
    protected $allowInlineLineBreaks;
    protected $ignoreEmptyContextAndExtra;
    protected $includeStacktraces;

    /**
     * @param string $format                     The format of the message
     * @param string $dateFormat                 The format of the timestamp: one supported by DateTime::format
     * @param bool   $allowInlineLineBreaks      Whether to allow inline line breaks in log entries
     * @param bool   $ignoreEmptyContextAndExtra
     */
    public function __construct($format = null, $dateFormat = null, $allowInlineLineBreaks = false, $ignoreEmptyContextAndExtra = false)
    {
        $this->format = $format ?: static::SIMPLE_FORMAT;
        $this->allowInlineLineBreaks = $allowInlineLineBreaks;
        //$this->ignoreEmptyContextAndExtra = $ignoreEmptyContextAndExtra;
        parent::__construct($dateFormat);
    }

    /**
     * {@inheritdoc}
     */
    public function format(array $record)
    {
        $vars = parent::format($record);
        $output = $this->format;

        foreach ($vars as $var => $val) {
            if (false !== strpos($output, '%'.$var.'%')) {
                $output = str_replace('%'.$var.'%', $this->stringify($val), $output);
            }
        }

        return $output;
    }

    public function stringify($value)
    {
        if (is_array($value)) {
            $str = var_export($value, true);
            $str = str_replace(['array (', "Array\n("], '[', $str);
            $str = str_replace(')', ']', $str);
            return $str;
        } else {
            return $this->replaceNewlines($this->convertToString($value));
        }
    }

    protected function convertToString($data)
    {
        if (null === $data || is_bool($data)) {
            return var_export($data, true);
        }

        if (is_scalar($data)) {
            return (string) $data;
        }

        if (version_compare(PHP_VERSION, '5.4.0', '>=')) {
            return $this->toJson($data, true);
        }

        return str_replace('\\/', '/', @json_encode($data));
    }

    protected function replaceNewlines($str)
    {
        if ($this->allowInlineLineBreaks) {
            if (0 === strpos($str, '{')) {
                return str_replace(['\r', '\n'], ["\r", "\n"], $str);
            }

            return $str;
        }

        return str_replace(["\r\n", "\r", "\n"], ' ', $str);
    }
}