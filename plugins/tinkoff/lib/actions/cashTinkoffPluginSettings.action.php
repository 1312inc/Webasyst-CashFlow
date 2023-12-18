<?php

class cashTinkoffPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $profile = waRequest::get('profile', 1, waRequest::TYPE_INT);
        $plugin = wa('cash')->getPlugin('tinkoff');
        $settings = $plugin->getSettings($profile);

        $this->view->assign('settings', $settings);
    }
}
