<?php

$m = new waModel();

try {
    $m->exec('select is_archived from cash_import');
} catch (Exception $ex) {
    $sql = <<<SQL
alter table cash_import add is_archived tinyint(1) default 0;
SQL;
    $m->exec($sql);
}
