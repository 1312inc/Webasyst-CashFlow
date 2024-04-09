<?php

/**
 * 2024-04-05 18:00:00
 */
$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_data` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_data` (
            `id` int(11) NOT NULL AUTO_INCREMENT,
            `sub_id` varchar(128) NOT NULL,
            `name` varchar(64) NOT NULL,
            `value` mediumtext NOT NULL,
            PRIMARY KEY (`id`),
            KEY `sub_id` (`sub_id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
