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
                    '%s tried to get into the Cash Flow app on %s but did not remember the master password and so initiated its reset. <b>Continue resetting your master password here</b>: <a href="%s"><b>%s</b></a> <br><br>If that wasn’t you, simply ignore this message. Your existing password is still active. <br><br>IP: %s, <br>User agent: %s',
                    wa()->getUser()->getName(),
                    wa()->accountName(),
                    $secret_link,
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
                    $this->errors[] = _w('The link has expired');
                }
            } elseif (!$access_code || !cashHelper::checkAccessCode($access_code)) {
                $this->errors[] = _w('Invalid master password');
                $text = sprintf_wp(
                    'One shall not pass! %s <b>attempted and failed to enter the valid master password</b> for the Cash Flow app on %s. <br>IP: %s, <br>User agent: %s',
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
                'forgot_dialog_text' => '<i class="fas fa-check-circle text-green"></i> ' . sprintf_wp('Check your email: %s', "<b>$email</b>"),
                'forgot_dialog_text_ext' => _w('If you don’t have access to this email, the only way to reset the password is to contact your team’s IT person to do this for you via root access privileges. For security purposes, there are no workarounds.')
            ]
        ]);
    }
}
