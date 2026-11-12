<?php

class cashSettingsSaveController extends cashJsonController
{
    public function execute()
    {
        if (!cash()->getUser()->isAdmin() || !cashHelper::isPremium()) {
            return null;
        }
        $data_master_password = waRequest::post('master', [], waRequest::TYPE_ARRAY);

        if ($data_master_password) {
            $this->masterPasswordSetting($data_master_password);
        }
    }

    private function masterPasswordSetting($data = [])
    {
        $settings_model = new waAppSettingsModel();
        if (ifset($data, 'disable_password', 0) == 1) {
            // disable master-code
            if (!isset($data['old_password']) || $data['old_password'] === '') {
                $this->errors = [_w('Не введен текущий пароль')];
                return false;
            }
            $access_code = $settings_model->get('cash', 'access_code', null);
            if (password_verify($data['old_password'], $access_code)) {
                $settings_model->del('cash', 'access_code');
                $settings_model->del('cash', 'email');
            } else {
                $this->errors = [_w('Введен неверный пароль')];
                return false;
            }
        } elseif (isset($data['old_password'], $data['new_password'], $data['confirm_password'])) {
            // update master-code
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->errors = [_w('Введенные пароли не совпали')];
                return false;
            }
            $access_code = $settings_model->get('cash', 'access_code', null);
            if (empty($data['email'])) {
                $this->errors = [_w('Email обязательный')];
                return false;
            } elseif (password_verify($data['old_password'], $access_code)) {
                $settings_model->set('cash', 'master_email', $data['email']);
                $settings_model->set('cash', 'access_code', password_hash($data['new_password'], PASSWORD_DEFAULT));
                wa()->getStorage()->remove('master_access');
            } else {
                $this->errors = [_w('Введен неверный пароль')];
                return false;
            }
        } elseif (isset($data['new_password'], $data['confirm_password'])) {
            //set master-code
            if ($data['new_password'] !== $data['confirm_password']) {
                $this->errors = [_w('Введенные пароли не совпали')];
                return false;
            } elseif (empty($data['email'])) {
                $this->errors = [_w('Email обязательный')];
                return false;
            } elseif ($data['new_password'] !== '') {
                $settings_model->set('cash', 'master_email', $data['email']);
                $settings_model->set('cash', 'access_code', password_hash($data['new_password'], PASSWORD_DEFAULT));
            }
        }

        return true;
    }
}
