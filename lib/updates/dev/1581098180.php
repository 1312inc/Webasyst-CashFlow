<?php

$m = new waModel();

try {
    $m->exec('select import_id from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add import_id int(11) null');
}
