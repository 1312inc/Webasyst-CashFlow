<?php

$m = new waModel();

try {
    $m->exec('select current_balance from cash_account');
    $m->exec('alter table cash_account drop column current_balance');
} catch (Exception $ex) {
}
