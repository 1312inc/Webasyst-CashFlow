<?php

$model = new waModel();

try {
    $model->exec("DROP TABLE IF EXISTS cash_template");
} catch (waException $e) {
}
