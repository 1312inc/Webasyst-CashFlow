<?php

$m = new waModel();

try {
    $m->exec('select contact_id from cash_import');
} catch (Exception $ex) {
    $sqls = [
        'alter table cash_import add contact_id int null after id',
        'alter table cash_import add success int default 0 null after params',
        'alter table cash_import add fail int default 0 null after success',
        'alter table cash_import add errors mediumtext null after fail',
    ];

    foreach ($sqls as $sql) {
        $m->exec($sql);
    }
}
