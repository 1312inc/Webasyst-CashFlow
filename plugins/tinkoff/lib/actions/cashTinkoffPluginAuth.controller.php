<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $answer = (new waServicesApi())->serviceCall('BANK', [
            'sub_path' => 'get_userinfo',
            'return_uri' => wa()->getRootUrl(true).wa()->getConfig()->getBackendUrl().'/cash/plugins/#/tinkoff'
        ]);
        $status = ifset($answer, 'status', 200);
        $response = ifset($answer, 'response', []);
        if (
            $status !== 200
            || ifset($response, 'http_code', 200) !== 200
        ) {
            // error
            $this->setError('Что-то пошло не так');
            waLog::log($answer,TINKOFF_FILE_LOG);
            return null;
        }

        $this->response = $response;
    }
}
