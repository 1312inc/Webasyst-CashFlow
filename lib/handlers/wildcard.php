<?php

return [
    [
        'event_app_id' => 'shop',
        'event' => 'order_action.*',
        'class' => 'cashShopOrderActionListener',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'cash',
        'event' => 'backend_import.file_uploaded',
        'class' => 'cashBackendImportListener',
        'method' => ['fileUploaded'],
    ],
    [
        'event_app_id' => 'cash',
        'event' => 'backend_transactions_export',
        'class' => 'cashBackendTransactionsExport',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'contacts',
        'event' => 'delete',
        'class' => 'cashContactsDelete',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'contacts',
        'event' => 'links',
        'class' => 'cashContactsDelete',
        'method' => ['links'],
    ],
];
