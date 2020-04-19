<?php

/**
 * Class cashSettingsSaveListener
 */
class cashSettingsSaveListener
{
    /**
     * @param cashEventSettingsSave $event
     */
    public function handleForecast(cashEventSettingsSave $event)
    {
        $oldSettings = $event->getOldSettings();
        $newSettings = $event->getNewSettings();


    }
}
