<?php

return [
    'import/' => 'import/',
    'report/dds/<type:(category|contractor|account)>/<year:((19|20)[\d]{2})>' => 'report/dds',
    'report/dds' => 'report/dds',
    'shop/settings/' => 'shop/settings',
    'loc/' => 'backend/loc',
    'plugins/?' => 'plugins/',
    '*' => 'backend/',
];
