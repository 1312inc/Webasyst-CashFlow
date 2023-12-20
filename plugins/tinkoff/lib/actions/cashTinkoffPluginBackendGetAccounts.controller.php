<?php

class cashTinkoffPluginBackendGetAccountsController extends waJsonController
{
    public function execute()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);
        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            $response = $plugin->getAccounts();
        }

        $accounts = [];
        foreach ($response as $_account) {
            $accounts[] = [
                'name'    => ifset($_account, 'name', ''),
                'default' => (ifset($_account, 'accountType', '') == 'Current')
            ];
        }

        $this->response = $accounts;
    }
}
