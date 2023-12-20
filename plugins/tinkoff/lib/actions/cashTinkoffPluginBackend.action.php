<?php

class cashTinkoffPluginBackendAction extends waViewAction
{
    protected function preExecute()
    {
        if (wa()->whichUI() === '2.0') {
            $this->setLayout(new cashStaticLayout());
        }

        if (!cash()->getUser()->isAdmin()) {
            throw new kmwaForbiddenException();
        }
    }

    public function execute()
    {
        /** @var cashTinkoffPlugin $plugin */
        $plugin = wa('cash')->getPlugin('tinkoff');
        $plugin_settings = $plugin->getSettings();
        $profiles = (array) ifset($plugin_settings, 'profiles', []);
        $this->view->assign([
            'profile_id' => key($profiles),
            'profiles'   => $profiles
        ]);
    }
}
