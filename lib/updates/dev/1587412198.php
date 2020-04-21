<?php

$m = new waModel();

try {
    $m->exec('select external_source from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add external_source varchar(20) null');
    $m->exec('create index cash_transaction_external_source_index on cash_transaction (external_source)');
}
