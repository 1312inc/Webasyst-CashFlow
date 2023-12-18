<?php

class cashTinkoffPluginBackendGetAccountsController extends waJsonController
{
    public function execute()
    {
        $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile' => 1]);
        $response = $plugin->getAccounts();

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
