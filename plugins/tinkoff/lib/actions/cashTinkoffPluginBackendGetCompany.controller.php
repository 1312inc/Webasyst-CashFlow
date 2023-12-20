<?php

class cashTinkoffPluginBackendGetCompanyController extends waJsonController
{
    public function execute()
    {
        $profile_id = waRequest::post('profile_id', 0, waRequest::TYPE_INT);
        if ($profile_id > 0) {
            $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile_id' => $profile_id]);
            $response = $plugin->getCompany();
        }

        $this->response = [
            'name'    => ifset($response, 'requisites', 'fullName', ''),
            'address' => ifset($response, 'requisites', 'address', '')
        ];
    }
}
