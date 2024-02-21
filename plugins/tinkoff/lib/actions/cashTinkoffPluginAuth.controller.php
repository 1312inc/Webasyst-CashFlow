<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $answer = (new waServicesApi())->serviceCall('BANK', [
            'sub_path' => 'get_userinfo',
'return_uri' => 'http://127.0.0.1:88/webasyst/cash/plugins/#/tinkoff'
        ]);
        $status = ifset($response, 'status', 200);
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
