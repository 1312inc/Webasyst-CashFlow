<?php

class cashTinkoffPluginBackendGetStatementController extends waJsonController
{
    public function execute()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        $this->response = [];
        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            try {
                $response = $plugin->getStatement();
                if (ifset($response, 'http_code', 200) !== 200) {
                    $error = implode(' ', [
                        ifset($response, 'errorMessage', ''),
                        ifset($response, 'errorDetails', ''),
                        ifset($response, 'error_description', '')
                    ]);
                    $this->setError($error);
                }
            } catch (Exception $ex) {
                $this->setError($ex->getMessage());
            }
            $this->response = (empty($response['operations']) ? [] : $plugin->addTransactions($response['operations']));
        }
    }
}
