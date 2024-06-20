<?php

return array(
    'name' => 'Т-Бизнес',
    'description' => 'Импорт операций из Т-Бизнеса',
    'img' => 'img/tinkoff.svg',
    'version' => '1.0.0',
    'vendor' => 'webasyst',
    'import_api' => true,
    'handlers' => [
        'on_count' => 'cashEventOnCountTinkoffHandler',
        'api_transaction_response_external_data' => 'cashEventApiTransactionExternalInfoTinkoffHandler'
    ]
);
