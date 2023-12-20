<?php

class cashTinkoffPluginBackendGetStatementController extends waJsonController
{
    public function execute()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        $this->response = [];
        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            $response = $plugin->getStatement();
            $this->response = (empty($response['operations']) ? [] : $plugin->addTransactions($response['operations']));
        }
    }
}
