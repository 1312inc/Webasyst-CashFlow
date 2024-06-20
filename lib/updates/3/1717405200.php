<?php

/**
 * 2024-06-03 12:00:00
 */
$m = new cashModel();

try {
    $m->exec("SELECT * FROM cash_account WHERE is_imaginary = 0");
} catch (Exception $ex) {
    $m->exec('ALTER TABLE cash_account ADD is_imaginary TINYINT NOT NULL DEFAULT 0');
}
