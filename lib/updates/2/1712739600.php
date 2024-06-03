<?php

/**
 * 2024-04-10 12:00:00
 */
$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_transaction_data` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_transaction_data` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `transaction_id` bigint(20) NOT NULL,
            `field_id` varchar(64) NOT NULL,
            `value` mediumtext NOT NULL,
            PRIMARY KEY (`id`),
            KEY `transaction_id` (`transaction_id`),
            KEY `field_id` (`field_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
