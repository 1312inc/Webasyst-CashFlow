<?php

$m = new waModel();

try {
    $m->exec('select transfer from cash_repeating_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_repeating_transaction add transfer text null');
}
