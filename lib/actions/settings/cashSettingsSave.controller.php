<?php

class cashSettingsSaveController extends cashJsonController
{
    public function execute()
    {
        if (!cash()->getUser()->isAdmin() || !cashHelper::isPremium()) {
            return null;
        }
        $data_master_password = waRequest::post('master', [], waRequest::TYPE_ARRAY);
        $data_master_password += [
            'current_password' => '',
            'new_password' => '',
            'confirm_password' => '',
            'disable_password' => null,
            'email' => ''
        ];

        if ($data_master_password) {
            $this->masterPasswordSetting($data_master_password);
        }
    }

    private function masterPasswordSetting($data = [])
    {
        $settings_model = new waAppSettingsModel();
        if (ifset($data, 'disable_password', 0) == 1) {
            // disable master-code
            if (empty($data['current_password'])) {
                $this->errors = [_w('Enter your existing password to confirm access')];
                return false;
            }
            $access_code = $settings_model->get('cash', 'access_code', null);
            if (password_verify($data['current_password'], $access_code)) {
                $settings_model->del('cash', 'access_code');
                $settings_model->del('cash', 'email');
            } else {
                $this->errors = [_w('Invalid master password')];
                return false;
            }
        } elseif (!empty($data['current_password'])) {
            $access_code = $settings_model->get('cash', 'access_code', null);
            if (empty($data['new_password']) && empty($data['confirm_password'])) {
                // update email
                if (empty($data['email'])) {
                    $this->errors = [_w('Recovery email is required')];
                    return false;
                } elseif (!password_verify($data['current_password'], $access_code)) {
                    $this->errors = [_w('Invalid master password')];
                    return false;
                }
                $settings_model->set('cash', 'master_email', $data['email']);
            } else {
                // set/update master-code
                if ($data['new_password'] !== $data['confirm_password']) {
                    $this->errors = [_w('Passwords do not match')];
                    return false;
                } elseif (empty($data['email'])) {
                    $this->errors = [_w('Recovery email is required')];
                    return false;
                } elseif (!password_verify($data['current_password'], $access_code)) {
                    $this->errors = [_w('Invalid master password')];
                    return false;
                }
                $settings_model->set('cash', 'master_email', $data['email']);
                $settings_model->set('cash', 'access_code', password_hash($data['new_password'], PASSWORD_DEFAULT));
                wa()->getStorage()->remove('master_access');
            }
        } elseif (!empty($data['email'])) {
            $this->errors = [_w('Enter your existing password to confirm access')];
            return false;
        }

        return true;
    }
}
