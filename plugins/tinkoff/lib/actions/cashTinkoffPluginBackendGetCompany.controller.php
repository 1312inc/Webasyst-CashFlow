<?php

class cashTinkoffPluginBackendGetCompanyController extends waJsonController
{
    public function execute()
    {
        $response = [];
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);

        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            try {
                $response = $plugin->getCompany();
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
        }

        $this->response = [
            'name'    => ifset($response, 'requisites', 'fullName', ''),
            'address' => ifset($response, 'requisites', 'address', '')
        ];
    }
}
