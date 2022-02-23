<?php

$m = new cashModel();

try {
    $m->exec('SELECT * FROM cash_transaction WHERE external_id = 1');
} catch (Exception $ex) {
    $m->exec('ALTER TABLE cash_transaction ADD external_id INT DEFAULT NULL NULL');
}

$data = $m
    ->query("SELECT * FROM cash_transaction WHERE external_source = 'shop' and external_id IS NULL ORDER BY id")
    ->fetchAll();
try {
    foreach ($data as $datum) {
        $externalData = json_decode($datum['external_data'], true);
        if (empty($externalData['id'])) {
            continue;
        }

        $m->exec(
            sprintf('UPDATE cash_transaction SET external_id = %d WHERE id = %d', $externalData['id'], $datum['id'])
        );
    }
} catch (Exception $ex) {
    waLog::log($ex->getMessage(), 'cash/update.log');
    waLog::log($ex->getTraceAsString(), 'cash/update.log');
}