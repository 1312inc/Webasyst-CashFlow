<?php

return [
    [
        'event_app_id' => 'shop',
        'event' => 'order_action.*',
        'class' => 'cashShopOrderActionListener',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'shop',
        'event' => 'backend_order',
        'class' => 'cashShopBackendOrderListener',
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
        'class' => 'cashContactsDeleteListener',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'contacts',
        'event' => 'links',
        'class' => 'cashContactsDeleteListener',
        'method' => ['links'],
    ],
    [
        'event_app_id' => 'contacts',
        'event' => 'profile.tab',
        'class' => 'cashContactsProfileTabListener',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'webasyst',
        'event' => 'backend_header',
        'class' => 'cashWebasystBackendHeaderListener',
        'method' => ['execute'],
    ],
    [
        'event_app_id' => 'cash',
        'event' => 'api_transaction_response_external_data',
        'class' => 'cashApiTransactionBeforeResponseListener',
        'method' => ['getExternalInfoHandlers'],
    ],
    [
        'event_app_id' => 'cash',
        'event' => 'backend_reports_menu_item',
        'class' => 'cashReportInternalMenuItemListener',
        'method' => ['handle'],
    ],
    [
        'event_app_id' => 'cash',
        'event' => 'backend_reports_handle',
        'class' => 'cashReportInternalHandlerListener',
        'method' => ['handle'],
    ],
];
