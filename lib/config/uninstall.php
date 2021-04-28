<?php

waLog::log('cashapp uninstall =(', 'cash/uninstall.log');

$m = new waModel();
$db = require wa('cash')->getConfig()->getAppConfigPath('db');

$innodb = 1;
try {
    waLog::log('disable all fk checks', 'cash/uninstall.log');
    $m->exec('SET FOREIGN_KEY_CHECKS = 0');
    $innodb = 1;

    foreach ($db as $tableName => $tableInfo) {
        waLog::log(sprintf('disable fk checks for table %s', $tableName), 'cash/uninstall.log');
        $m->exec(sprintf('ALTER TABLE %s DISABLE KEYS', $tableName));

        waLog::log(sprintf('drop table %s', $tableName), 'cash/uninstall.log');
        $m->exec('DROP TABLE IF EXISTS ' . $tableName);
    }
} catch (Exception $ex) {
    waLog::log($ex->getMessage());
    waLog::log($ex->getTraceAsString());
} finally {
    if ($innodb) {
        waLog::log('enable all fk checks', 'cash/uninstall.log');
        $m->exec('SET FOREIGN_KEY_CHECKS = 1');
    }
}

waLog::log('cashapp uninstall done. hope see you later', 'cash/uninstall.log');
