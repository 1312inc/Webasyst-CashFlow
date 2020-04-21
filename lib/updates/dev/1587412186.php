<?php

$m = new waModel();

try {
    $m->exec('select cash_transaction from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add external_hash varchar(32) null');
    $m->exec('create index cash_transaction_external_hash_index on cash_transaction (external_hash)');
}
