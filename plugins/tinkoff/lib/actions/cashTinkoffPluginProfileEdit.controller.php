<?php

class cashTinkoffPluginProfileEditController extends waJsonController
{
    public function execute()
    {
        $profile_settings = [];
        $profile_id = waRequest::get('profile_id', 1, waRequest::TYPE_INT);
        $tinkoff_id = waRequest::get('tinkoff_id', '', waRequest::TYPE_STRING_TRIM);
        $last_connect_date = waRequest::get('last_connect_date', date('Y-m-d H:i:s'), waRequest::TYPE_STRING_TRIM);

        if ($profile_id > 0) {
            /** @var cashTinkoffPlugin $plugin */
            $plugin = wa()->getPlugin('tinkoff');
            $profile_settings = $plugin->getProfiles($profile_id);

            $profile_settings = [
                'tinkoff_id' => $tinkoff_id,
                'last_connect_date' => $last_connect_date
            ] + $profile_settings;
            $plugin->saveProfiles($profile_id, $profile_settings);
        }

        $this->response = $profile_settings;
    }
}
