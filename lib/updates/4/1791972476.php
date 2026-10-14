<?php

$model = new waModel();

try {
    $model->query("SELECT scenario_id FROM `cash_repeating_transaction`");
} catch (waException $e) {
    $model->exec('ALTER TABLE cash_repeating_transaction ADD scenario_id int NULL AFTER category_id');
}
