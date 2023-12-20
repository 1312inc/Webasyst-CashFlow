<?php

class cashTinkoffPluginSettingsDeleteController extends waJsonController
{
    public function execute()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);
        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile' => $profile_id]);
            $plugin_settings = $plugin->getSettings();
            unset($plugin_settings['profiles'][$profile_id]);
            $plugin->saveSettings($plugin_settings);
        }

        $this->response = [];
    }
}
