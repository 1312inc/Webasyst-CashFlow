<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_company` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_company` (
            `id` int NOT NULL AUTO_INCREMENT,
            `name` varchar(32) DEFAULT '',
            `sort` int DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
