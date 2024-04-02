<?php

class cashTinkoffPluginProfileEditController extends waJsonController
{
    /**
     * @param $profile_id
     * @param $tinkoff_id
     * @param $last_connect_date
     * @return void
     * @throws waException
     */
    public function execute($profile_id = null, $profiles = [])
    {
        $profile_settings = [];
        $profiles = (array) waRequest::request('profiles', $profiles, waRequest::TYPE_ARRAY);
        $profile_id = (int) waRequest::request('profile_id', $profile_id, waRequest::TYPE_INT);

        if ($profile_id > 0) {
            $data = [];
            /** @var cashTinkoffPlugin $plugin */
            $plugin = wa()->getPlugin('tinkoff');
            $profile_settings = ifset($profiles, $profile_id, []) + $plugin->getProfile($profile_id);
            $plugin->saveProfile($profile_id, $data + $profile_settings);
        }

        $this->response = $profile_settings;
    }
}
