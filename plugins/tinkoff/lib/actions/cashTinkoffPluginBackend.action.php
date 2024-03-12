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
        $plugin = wa()->getPlugin('tinkoff');
        $profiles = $plugin->getProfiles();
        $this->view->assign([
            'profile_id'   => key($profiles),
            'profile'      => ifset($profiles, key($profiles), []),
            'settings_url' => wa()->getRootUrl(true).wa()->getConfig()->getBackendUrl().'/cash/plugins/#/tinkoff'
        ]);
    }
}
