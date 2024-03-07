<?php

class cashTinkoffPluginBackendGetCompanyController extends waJsonController
{
    public function execute()
    {
        $response = [];
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ($profile_id > 0) {
            try {
                /** @var cashTinkoffPlugin $plugin */
                $plugin = wa()->getPlugin('tinkoff');
                $answer = $plugin->getCompany();
                $status = ifset($answer, 'status', 200);
                $response = ifset($answer, 'response', 'company_info', []);
                if (
                    $status !== 200
                    || ifset($response, 'http_code', 200) !== 200
                ) {
                    $error = implode(' ', [
                        ifset($response, 'errorMessage', ''),
                        ifset($response, 'errorDetails', ''),
                        ifset($response, 'error_description', '')
                    ]);
                    $this->setError($error);
                }
            } catch (Exception $ex) {
                $this->setError($ex->getMessage());
                waLog::log($ex->getMessage(), TINKOFF_FILE_LOG);
            }
        }

        $this->response = [
            'name'    => ifset($response, 'requisites', 'fullName', ''),
            'address' => ifset($response, 'requisites', 'address', '')
        ];
    }
}
