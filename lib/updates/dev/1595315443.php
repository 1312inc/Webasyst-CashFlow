<?php

$m = new waModel();

try {
    $m->exec('select contractor_contact_id from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add contractor_contact_id int null');
}

try {
    $m->exec('select contractor_contact_id from cash_repeating_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_repeating_transaction add contractor_contact_id int null');
}
