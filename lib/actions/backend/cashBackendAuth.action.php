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
