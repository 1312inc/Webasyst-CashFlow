<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_plan` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_plan` (
            `id` int NOT NULL AUTO_INCREMENT,
            `currency` varchar(3) NOT NULL,
            `account_id` int DEFAULT NULL,
            `category_id` int NOT NULL,
            `month` date,
            `amount` decimal(18,4) NOT NULL DEFAULT '0.0000',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
