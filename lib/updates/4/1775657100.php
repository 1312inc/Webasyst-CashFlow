<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_automation` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_automation` (
            `id` int NOT NULL AUTO_INCREMENT,
            `sort` int NOT NULL DEFAULT '0',
            `app_id` varchar(64) NOT NULL,
            `action_id` varchar(255) NOT NULL,
            `conditions` text,
            `rule_data` text,
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
