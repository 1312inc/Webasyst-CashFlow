<?php

$model = new cashModel();

try {
    $model->exec('create index cash_transaction_contractor_contact_id_index on cash_transaction (contractor_contact_id)');
} catch (Exception $ex) {

}
