<?php

$m = new waModel();
$db = require wa('cash')->getConfig()->getAppConfigPath('db');

$innodb = 1;
foreach ($db as $table => $info) {
    try {
        $m->exec('SET FOREIGN_KEY_CHECKS = 0');
        $innodb = 1;
        foreach ($db as $tableName => $tableInfo) {
            $m->exec('drop table if exists ' . $tableName);
        }
    } catch (Exception $ex) {
        waLog::log($ex->getMessage());
        waLog::log($ex->getTraceAsString());
    } finally {
        if ($innodb) {
            $m->exec('SET FOREIGN_KEY_CHECKS = 1');
        }
    }
}
