<?php

$m = new cashModel();

try {
    $m->exec('SELECT is_self_destruct_when_due FROM cash_transaction LIMIT 1');
} catch (Exception $ex) {
    $m->exec('ALTER TABLE cash_transaction ADD is_self_destruct_when_due TINYINT DEFAULT 0');
    $m->exec(
        "UPDATE cash_transaction SET is_self_destruct_when_due = 1 WHERE external_hash = 'forecast' AND external_source = 'shop'"
    );
}
