<?php

if (!defined('TINKOFF_FILE_LOG')) {
    define('TINKOFF_FILE_LOG', 'cash/tinkoff.log');
}

return array(
    'name' => 'Tinkoff API',
    'description' => 'Импорт выписки из Тинькофф банка',
    'img' => 'img/logo.svg',
    'version' => '0.0.1',
    'vendor' => 'webasyst',
    'import_api' => true,
    'custom_settings' => true
);
