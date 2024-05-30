<?php

if (!defined('TINKOFF_FILE_LOG')) {
    define('TINKOFF_FILE_LOG', 'cash/tinkoff.log');
}

return array(
    'name' => 'Тинькофф Бизнес',
    'description' => 'Импорт операций из «Тинькофф Бизнес»',
    'img' => 'img/tinkoff.svg',
    'version' => '1.0.0',
    'vendor' => 'webasyst',
    'import_api' => true,
    'handlers' => [
        'on_count' => 'cashEventOnCountTinkoffHandler',
        'api_transaction_response_external_data' => 'cashEventApiTransactionExternalInfoTinkoffHandler'
    ]
);
