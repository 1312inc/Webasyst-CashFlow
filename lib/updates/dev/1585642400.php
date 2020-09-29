<?php

$m = new waModel();

try {
    $m->exec('select is_archived from cash_transaction');
} catch (Exception $ex) {
    $m->exec('alter table cash_transaction add is_archived tinyint(1) default 0');
    $m->exec('create index cash_transaction_is_archived_index on cash_transaction (is_archived)');
}
