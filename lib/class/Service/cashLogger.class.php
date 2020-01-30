<?php

/**
 * Class cashLogger
 */
class cashLogger
{
    /**
     * @param $message
     */
    public function debug($message)
    {
        if (waSystemConfig::isDebug()) {
            if (is_string($message)) {
                $this->log($message, 'debug');
            } else {
                $this->log($message, 'debug');
            }
        }
    }

    /**
     * @param        $message
     * @param string $file
     */
    public function log($message, $file = 'log')
    {
        if (waSystemConfig::isDebug()) {
            if (is_string($message)) {
                waLog::log($message, sprintf('cash/%s.log', $file));
            } else {
                waLog::dump($message, sprintf('cash/%s.log', $file));
            }
        }
    }

    /**
     * @param string         $message
     * @param Exception|null $exception
     */
    public function error($message, Exception $exception = null)
    {
        $this->log($message, 'error');
        if ($exception instanceof Exception) {
            if ($message !== $exception->getMessage()) {
                $this->log($exception->getMessage(), 'error');
            }
            $this->log($exception->getTraceAsString(), 'error');
        }
    }
}
