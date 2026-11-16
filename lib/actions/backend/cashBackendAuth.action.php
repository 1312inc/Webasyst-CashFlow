<?php

class cashBackendAuthAction extends waViewAction
{
    /**
     * @var array
     */
    private $errors = [];

    public function execute()
    {
        if (waRequest::isXMLHttpRequest()) {
            $access_code = waRequest::post('access_pass', '', waRequest::TYPE_STRING);
            if (!$access_code || !cashHelper::checkAccessCode($access_code)) {
                $this->errors[] = _w('Invalid master password');
                $master_code = (new waAppSettingsModel())->get('cash', 'master_email', null);
                $text = sprintf_wp(
                    'Ахтунг! Такой-то юзернейм %s пробовал проникнуть в "Деньги", но ввел неверный мастер-пароль<br>IP: %s,<br>User agent: %s',
                    wa()->getUser()->getName(),
                    waRequest::getIp(),
                    waRequest::getUserAgent()
                );
                cashHelper::sendEmail($master_code, _w('Система оповещения приложения "Деньги"'), $text);
            }

            $this->getResponse()->addHeader('Content-type', 'application/json');
            $this->getResponse()->sendHeaders();

            if ($this->errors) {
                echo waUtils::jsonEncode(['status' => 'fail', 'errors' => $this->errors]);
            } else {
                echo waUtils::jsonEncode(['status' => 'ok', 'data' => []]);
            }
            exit;
        } elseif (cashHelper::checkAccessCode() !== false) {
            $this->redirect(wa()->getAppUrl('cash'));
        }
    }
}
