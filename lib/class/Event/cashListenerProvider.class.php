<?php

/**
 * Class cashListenerProvider
 */
class cashListenerProvider extends kmwaWaListenerProviderAbstract
{
    /**
     * @return string
     */
    protected function getAppName()
    {
        return cashConfig::APP_ID;
    }
}
