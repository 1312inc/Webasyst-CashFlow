<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $ref = waRequest::get('ref', '', waRequest::TYPE_STRING_TRIM);
        $return_uri = rtrim(wa()->getRootUrl(true), DIRECTORY_SEPARATOR).wa()->getAppUrl().($ref === 'import' ? '?plugin=tinkoff' : 'plugins/#/tinkoff');
        $answer = (new waServicesApi())->serviceCall('BANK', [
            'sub_path' => 'get_userinfo',
            'return_uri' => $return_uri
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
