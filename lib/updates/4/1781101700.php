<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_automation_log` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_automation_log` (
            `id` int NOT NULL AUTO_INCREMENT,
            `datetime` datetime NOT NULL,
            `automation_id` int NOT NULL,
            `transaction_id` int NOT NULL,
            `type` enum('normal', 'notice', 'warning', 'error') NOT NULL DEFAULT 'normal',
            `plugin_id` varchar(63) NULL,
            `automation_event` varchar(255) NULL,
            `automation_action` varchar(255) NULL,
            `description` varchar(4095) NULL,
            `automation_rule_json` text,
            `transaction_json` text,
            PRIMARY KEY (`id`),
            KEY `automation_id` (`automation_id`),
            KEY `transaction_id` (`transaction_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
