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

try {
    $model->query("SELECT company_id FROM `cash_account`");
} catch (waException $e) {
    $model->exec('ALTER TABLE cash_account ADD company_id int NULL AFTER currency');
}
