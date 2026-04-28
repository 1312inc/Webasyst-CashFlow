<?php

$model = new waModel();

try {
    $model->exec("SELECT accountable_contact_id FROM cash_account");
} catch (Exception $ex) {
    $model->exec('ALTER TABLE cash_account ADD accountable_contact_id int NULL AFTER customer_contact_id');
}
