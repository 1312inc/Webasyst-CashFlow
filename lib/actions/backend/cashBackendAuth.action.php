<?php

class cashBackendAuthAction extends waViewAction
{
    /**
     * @var array
     */
    private $errors = [];

    public function execute()
    {
        $master_email = (new waAppSettingsModel())->get('cash', 'master_email', null);
        if (waRequest::isXMLHttpRequest()) {
            $access_code = waRequest::post('access_pass', '', waRequest::TYPE_STRING);
            $forgot_password = waRequest::post('forgot_password', 0, waRequest::TYPE_INT);
            if ($forgot_password) {
                $hash = md5($master_email).md5(waRequest::getIp().waRequest::getUserAgent().time());
                $secret_link = wa()->getRootUrl(true).wa()->getConfig()->getBackendUrl().'/cash/?access_code_reset='.$hash;
                $text = sprintf_wp(
                    '%s из вашей команды пробовал проникнуть в "Деньги" на %s и запросил новый пароль. Перегенерить пароль можно тут: <a href="%s">Задать новый пароль</a><br>IP: %s, <br>User agent: %s',
                    wa()->getUser()->getName(),
                    wa()->accountName(),
                    $secret_link,
                    waRequest::getIp(),
                    waRequest::getUserAgent()
                );
                cashHelper::sendEmail($master_email, _w('Cash Flow: master password alert!'), $text);
                cash()->getCache()->set('cash_forgot_password_hash', $hash, 300);
            } elseif ($reset_hash = waRequest::post('access_code_reset', false)) {
                if ($reset_hash == cash()->getCache()->get('cash_forgot_password_hash')) {
                    $new_pass = waRequest::post('new_pass', false);
                    $confirm_pass = waRequest::post('confirm_pass', false);
                    if (
                        empty($new_pass)
                        || empty($confirm_pass)
                        || $new_pass !== $confirm_pass
                    ) {
                        $this->errors[] = _w('Passwords do not match');
                    } else {
                        (new waAppSettingsModel())->set('cash', 'access_code', password_hash($new_pass, PASSWORD_DEFAULT));
                        cash()->getCache()->delete('cash_forgot_password_hash');
                    }
                } else {
                    $this->errors[] = _w('К сожалению время на смену пароля по этой ссылке истекло');
                }
            } elseif (!$access_code || !cashHelper::checkAccessCode($access_code)) {
                $this->errors[] = _w('Invalid master password');
                $text = sprintf_wp(
                    'One shall not pass! <b>%s attemted and failed to enter the valid master password</b> for the Cash Flow app on <b>%s</b>. <br>IP: %s, <br>User agent: %s',
                    wa()->getUser()->getName(),
                    wa()->accountName(),
                    waRequest::getIp(),
                    waRequest::getUserAgent()
                );
                cashHelper::sendEmail($master_email, _w('Cash Flow: master password fail alert!'), $text);
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
        } elseif ($reset_hash = waRequest::get('access_code_reset', false)) {
            $hash_expired = true;
            if ($reset_hash === cash()->getCache()->get('cash_forgot_password_hash')) {
                $hash_expired = false;
            }
        }

        $email = preg_replace('#(?<=[a-zA-Zа-яА-ЯёЁ0-9])[a-zA-Zа-яА-ЯёЁ0-9]#u', '*', $master_email);
        $this->view->assign([
            'forgot_password' => !empty($reset_hash),
            'hash_expired' => $hash_expired ?? true,
            'locale' => [
                'forgot_dialog_text' => sprintf_wp('Письмо со ссылкой на сброс пароля отправлено по адресу %s', "<div class='align-center bold h4'>$email</div>"),
                'forgot_dialog_text_ext' => _w('Если нет доступа к почте, то сбросить пароль вы можете только имея доступ к БД на сервере. Дас ис фюр потребляйтен защитен.')
            ]
        ]);
    }
}
