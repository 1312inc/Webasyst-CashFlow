<?php

$m = new waModel();

try {
    $m->exec('select external_data from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add external_data text null');
}
