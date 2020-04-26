<?php

$m = new waModel();

try {
    $m->exec('select external_hash from cash_repeating_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_repeating_transaction add external_hash varchar(32) null');
    $m->exec('create index cash_transaction_external_hash_index on cash_repeating_transaction (external_hash)');
}

try {
    $m->exec('select external_source from cash_repeating_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_repeating_transaction add external_source varchar(20) null');
    $m->exec('create index cash_transaction_external_source_index on cash_repeating_transaction (external_source)');
}
