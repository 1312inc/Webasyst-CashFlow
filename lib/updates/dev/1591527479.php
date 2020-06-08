<?php

$m = new waModel();

try {
    $m->exec('select transfer from cash_repeating_transaction');
    $m->exec('alter table cash_repeating_transaction drop column transfer');
} catch (Exception $ex) {
}
