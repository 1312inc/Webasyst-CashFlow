<?php

class cashSettingsSaveController extends cashJsonController
{
    private $settings_model;

    public function execute()
    {
        if (!cash()->getUser()->isAdmin() || !cashHelper::isPremium()) {
            return null;
        }
        $this->settings_model = new waAppSettingsModel();

        $this->response = $this->masterPasswordSetting() && $this->bigBangDateSetting();
    }

    private function masterPasswordSetting()
    {
        $data = waRequest::post('master', [], waRequest::TYPE_ARRAY) + [
            'current_password' => '',
            'new_password'     => '',
            'confirm_password' => '',
            'disable_password' => null,
            'email'            => ''
        ];
        if (ifset($data, 'disable_password', 0) == 1) {
            // disable master-code
            if (empty($data['current_password'])) {
                $this->errors = [_w('Enter your existing password to confirm access')];
                return false;
            }
            $access_code = $this->settings_model->get('cash', 'access_code', null);
            if (password_verify($data['current_password'], $access_code)) {
                $email = $this->settings_model->get('cash', 'master_email', null);
                $this->settings_model->del('cash', 'access_code');
                $this->settings_model->del('cash', 'master_email');
                $message = sprintf_wp(
                    'Так, а вот тут стопэ! %s отключил мастер-пароль на приложение "Деньги". Будьте блительны.<br>IP: %s,<br>User agent: %s',
                    cash()->getUser()->getName(),
                    waRequest::getIp(),
                    waRequest::getUserAgent(),
                );
            } else {
                $this->errors = [_w('Invalid master password')];
                return false;
            }
        } else {
            // set/update master-code

            $access_code = $this->settings_model->get('cash', 'access_code', null);
            $master_email = $this->settings_model->get('cash', 'master_email', null);

            if ($access_code) {
                // update master-code

                if (empty($data['email'])) {
                    $this->errors = [_w('Recovery email is required')];
                    return false;
                }
                if ($master_email !== $data['email']) {
                    // update email
                    if (empty($data['current_password'])) {
                        $this->errors = [_w('Enter your existing password to confirm access')];
                        return false;
                    } elseif (password_verify($data['current_password'], $access_code)) {
                        $this->settings_model->set('cash', 'master_email', $data['email']);
                        $email = $master_email;
                        $message = sprintf_wp(
                            '%s в приложении приложении "Деньги" поменял админский емаил с %s -> %s Смотрите в оба!.<br>IP: %s,<br>User agent: %s',
                            cash()->getUser()->getName(),
                            $master_email,
                            $data['email'],
                            waRequest::getIp(),
                            waRequest::getUserAgent(),
                        );
                    } else {
                        $this->errors = [_w('Invalid master password')];
                        return false;
                    }
                }

                if (!empty($data['new_password']) && !empty($data['confirm_password'])) {
                    // update master-code
                    if (empty($data['current_password'])) {
                        $this->errors = [_w('Enter your existing password to confirm access')];
                        return false;
                    } elseif ($data['new_password'] !== $data['confirm_password']) {
                        $this->errors = [_w('Passwords do not match')];
                        return false;
                    } elseif (password_verify($data['current_password'], $access_code)) {
                        $this->settings_model->set('cash', 'access_code', password_hash($data['new_password'], PASSWORD_DEFAULT));
                        wa()->getStorage()->remove('master_access');
                        $email = $master_email;
                        $message = sprintf_wp(
                            'А пароль-то новый! %s поменял мастер-пароль на приложение "Деньги".<br>IP: %s,<br>User agent: %s',
                            cash()->getUser()->getName(),
                            waRequest::getIp(),
                            waRequest::getUserAgent(),
                        );
                    } else {
                        $this->errors = [_w('Invalid master password')];
                        return false;
                    }
                }
            } elseif (!empty($data['new_password'])) {
                // set master-code
                if ($data['new_password'] !== $data['confirm_password']) {
                    $this->errors = [_w('Passwords do not match')];
                    return false;
                } elseif (empty($data['email'])) {
                    $this->errors = [_w('Recovery email is required')];
                    return false;
                }
                $this->settings_model->set('cash', 'master_email', $data['email']);
                $this->settings_model->set('cash', 'access_code', password_hash($data['new_password'], PASSWORD_DEFAULT));
                wa()->getStorage()->remove('master_access');
                $email = $data['email'];
                $message = sprintf_wp(
                    'Теперь все в домике! %s установил мастер-пароль на приложение "Деньги".<br>IP: %s,<br>User agent: %s',
                    cash()->getUser()->getName(),
                    waRequest::getIp(),
                    waRequest::getUserAgent(),
                );
            }
        }

        if (!empty($message) && !empty($email)) {
            $subject = _w('Система оповещения приложения "Деньги"');
            cashHelper::sendEmail($email, $subject, $message);
        }

        return true;
    }

    private function bigBangDateSetting()
    {
        $big_bang_date = waRequest::post('bigbang', '', waRequest::TYPE_STRING_TRIM);
        if ($big_bang_date) {
            $this->settings_model->set('cash', 'big_bang_date', $big_bang_date);
        } else {
            $this->settings_model->del('cash', 'big_bang_date');
        }

        return true;
    }
}
