<?php

$model = new waModel();

try {
    $model->query("SELECT * FROM `cash_template` WHERE 0");
} catch (waException $e) {
    $model->exec("
        CREATE TABLE IF NOT EXISTS `cash_template` (
            `id` int NOT NULL AUTO_INCREMENT,
            `contact_id` INT(11) DEFAULT NULL,
            `name` varchar(64) NOT NULL,
            `content` mediumtext NOT NULL,
            `sort` int NOT NULL DEFAULT '0',
            PRIMARY KEY (`id`)
        ) ENGINE=MyISAM  DEFAULT CHARSET=utf8
    ");
}
