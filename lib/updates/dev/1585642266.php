<?php

$m = new waModel();

try {
    $m->exec('select provider from cash_import');
} catch (Exception $ex) {
    $sql = <<<SQL
alter table cash_import add provider varchar(50) default 'csv';
SQL;
    $m->exec($sql);
}
