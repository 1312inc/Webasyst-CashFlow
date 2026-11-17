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
                    'One shall not pass! <b>%s attemted and failed to enter the valid master password</b> for the Cash Flow app on <b>%s</b>. <br>IP: %s, <br>User agent: %s',
                    wa()->getUser()->getName(),
                    wa()->accountName(),
                    waRequest::getIp(),
                    waRequest::getUserAgent()
                );
                cashHelper::sendEmail($master_code, _w('Cash Flow: master password fail alert!'), $text);
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
