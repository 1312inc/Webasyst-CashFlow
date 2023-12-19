<?php

class cashTinkoffPluginBackendGetStatementController extends waJsonController
{
    public function execute()
    {
        $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile' => 1]);
        $response = $plugin->getStatement();

        $this->response = (empty($response['operations']) ? [] : $plugin->addTransactions($response['operations']));
    }
}
