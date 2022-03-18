<?php
return [
    'name' => 'Flow',
    'img' => 'img/flow.gif',
    'version' => '1.0.0',
    'vendor' => '--',
    'handlers' =>
        [
            '*' => [
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_reports_menu_item',
                    'class' => 'cashFlowPluginMenuItemListener',
                    'method' => ['handle'],
                ],
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_reports_handle',
                    'class' => 'cashFlowPluginHandlerListener',
                    'method' => ['handle'],
                ],
            ],
        ],
];
