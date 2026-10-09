<?php

$model = new waModel();

try {
    $model->exec('ALTER TABLE cash_scenario MODIFY COLUMN color varchar(7) NULL');
} catch (waException $e) {
}
