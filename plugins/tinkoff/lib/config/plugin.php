<?php

if (!defined('TINKOFF_FILE_LOG')) {
    define('TINKOFF_FILE_LOG', 'cash/tinkoff.log');
}

return array(
    'name' => 'Тинькофф Бизнес',
    'description' => 'Импорт операций из «Тинькофф Бизнес» по API',
    'img' => 'img/tinkoff.svg',
    'version' => '0.0.1',
    'vendor' => 'webasyst',
    'import_api' => true,
    'custom_settings' => true
);
