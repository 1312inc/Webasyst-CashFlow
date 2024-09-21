<?php

return array(
    'name' => 'Т-Бизнес',
    'description' => 'Импорт операций из Т-Бизнеса',
    'img' => 'img/tinkoff.svg',
    'version' => '1.0.2',
    'vendor'  => '1021997',
    'import_api' => true,
    'handlers' => [
        'on_count' => 'cashEventOnCountTinkoffHandler',
        'api_transaction_response_external_data' => 'cashEventApiTransactionExternalInfoTinkoffHandler'
    ]
);
