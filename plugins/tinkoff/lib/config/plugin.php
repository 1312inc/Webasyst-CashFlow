<?php
return [
    'name' => 'Tinkoff',
    'img' => 'img/tinkoff.gif',
    'version' => '0.0.1',
    'vendor' => '--',
    'handlers' =>
        [
            '*' => [
                [
                    'event_app_id' => 'cash',
                    'event' => 'api_transaction_response_external_data',
                    'class' => 'cashTinkoffPlugin',
                    'method' => 'getExternalInfoHandler',
                ],
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_imports_menu_item',
                    'class' => 'cashTinkoffPluginImportMenuItemListener',
                    'method' => ['handle'],
                ],
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_imports_handle',
                    'class' => 'cashTinkoffPluginImportHandlerListener',
                    'method' => ['handle'],
                ],
            ],
        ],
];
