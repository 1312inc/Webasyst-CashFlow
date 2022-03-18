<?php
return [
    'name' => 'Dds',
    'img' => 'img/dds.gif',
    'version' => '1.0.0',
    'vendor' => '--',
    'handlers' =>
        [
            '*' => [
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_reports_menu_item',
                    'class' => 'cashDdsPluginMenuItemListener',
                    'method' => ['handle'],
                ],
                [
                    'event_app_id' => 'cash',
                    'event' => 'backend_reports_handle',
                    'class' => 'cashDdsPluginHandlerListener',
                    'method' => ['handle'],
                ],
            ],
        ],
];
