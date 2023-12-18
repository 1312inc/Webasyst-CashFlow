<?php

/**
 * 2023-12-15 18:00:00
 */
$_model = new cashModel();

try {
    $_model->query("ALTER TABLE cash_transaction MODIFY COLUMN external_hash varchar(128) NULL");
} catch (waDbException $e) {
    //
}
