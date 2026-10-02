<?php

$model = new waModel();

try {
    $model->query("SELECT company_id FROM `cash_plan`");
} catch (waException $e) {
    $model->exec('ALTER TABLE cash_plan ADD company_id int NULL AFTER currency');
}
