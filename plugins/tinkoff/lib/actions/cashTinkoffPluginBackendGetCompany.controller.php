<?php

class cashTinkoffPluginBackendGetCompanyController extends waJsonController
{
    public function execute()
    {
        $plugin = new cashTinkoffPlugin(['id' => 'tinkoff', 'profile' => 1]);
        $response = $plugin->getCompany();

        $this->response = [
            'name'    => ifset($response, 'requisites', 'fullName', ''),
            'address' => ifset($response, 'requisites', 'address', '')
        ];
    }
}
