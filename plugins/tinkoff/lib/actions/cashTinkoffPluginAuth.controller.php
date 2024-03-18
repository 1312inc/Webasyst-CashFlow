<?php

class cashTinkoffPluginAuthController extends waJsonController
{
    public function execute()
    {
        $ref = waRequest::get('ref', '', waRequest::TYPE_STRING_TRIM);
        $tinkoff_id = waRequest::get('tinkoff_id', '', waRequest::TYPE_STRING_TRIM);

        $backend_url = rtrim(wa()->getRootUrl(true), DIRECTORY_SEPARATOR).wa()->getAppUrl();
        if (empty($tinkoff_id)) {
            /** no auth */
            wa()->getStorage()->del('cash.tinkoff_back_redirect');
            wa()->getStorage()->set('cash.tinkoff_back_redirect', $backend_url.($ref === 'import' ? '?plugin=tinkoff' : 'plugins/#/tinkoff'));
            $answer = (new waServicesApi())->serviceCall('BANK', [
                'sub_path' => 'get_userinfo',
                'return_uri' => $backend_url.'?plugin=tinkoff&module=auth'
            ]);
        } else {
            (new cashTinkoffPluginProfileEditController())->execute(1, $tinkoff_id, date('Y-m-d H:i:s'));
            $redirect_url = wa()->getStorage()->get('cash.tinkoff_back_redirect');
            if ($redirect_url) {
                $this->redirect($redirect_url);
            }
            $this->redirect($backend_url);
        }
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
