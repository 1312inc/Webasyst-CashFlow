<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_scenario` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_scenario` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(32) NOT NULL,
            `color` varchar(6) NULL,
            `sort` int NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}

try {
    $model->query("SELECT scenario_id FROM `cash_transaction`");
} catch (waException $e) {
    $model->exec('ALTER TABLE cash_transaction ADD scenario_id int NULL AFTER repeating_id');
}
