<?php

class cashTinkoffPluginProfileEditController extends waJsonController
{
    public function execute()
    {
        $profile_settings = [];
        $profiles = (array) waRequest::request('profiles', [], waRequest::TYPE_ARRAY);
        $profile_id = waRequest::request('profile_id', null, waRequest::TYPE_INT);
        $tinkoff_id = waRequest::request('tinkoff_id', null, waRequest::TYPE_STRING_TRIM);
        $enable_import = waRequest::request('enable_import', null, waRequest::TYPE_STRING_TRIM);
        $last_connect_date = waRequest::request('last_connect_date', null, waRequest::TYPE_STRING_TRIM);

        if ($profile_id > 0) {
            $data = [];
            /** @var cashTinkoffPlugin $plugin */
            $plugin = wa()->getPlugin('tinkoff');
            $profile_settings = ifset($profiles, $profile_id, []) + $plugin->getProfile($profile_id);

            if (!is_null($tinkoff_id)) {
                $data['tinkoff_id'] = $tinkoff_id;
            }
            if (!is_null($enable_import)) {
                $data['enable_import'] = (bool) $enable_import;
            }
            if (!is_null($last_connect_date)) {
                $data['last_connect_date'] = $last_connect_date;
            }
            $plugin->saveProfile($profile_id, $data + $profile_settings);
        }

        $this->response = $profile_settings;
    }
}
