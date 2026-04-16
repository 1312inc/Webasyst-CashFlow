<?php

$model = new waModel();

try {
    $model->exec("DROP TABLE IF EXISTS cash_plan");
} catch (waException $e) {
}
