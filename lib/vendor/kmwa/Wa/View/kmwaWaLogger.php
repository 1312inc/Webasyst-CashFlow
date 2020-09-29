<?php

/**
 * Class kmwaWaLogger
 */
class kmwaWaLogger
{
    /**
     * @param mixed  $msg
     * @param string $file
     * @param string $app
     */
    public static function debug($msg, $file = 'debug.log', $app = '')
    {
        if (waSystemConfig::isDebug()) {
            self::log(is_string($msg) ? $msg : print_r($msg, 1), $file, $app);
        }
    }

    /**
     * @param mixed  $msg
     * @param string $file
     * @param string $app
     */
    public static function log($msg, $file = 'log.log', $app = '')
    {
        $path = $app ?: wa()->getApp();
        waLog::log(is_string($msg) ? $msg : print_r($msg, 1), $path.'/'.$file);
    }

    /**
     * @param mixed  $msg
     * @param string $file
     * @param string $app
     */
    public static function error($msg, $file = 'error.log', $app = '')
    {
        self::log(is_string($msg) ? $msg : print_r($msg, 1), $file, $app);
    }
}
