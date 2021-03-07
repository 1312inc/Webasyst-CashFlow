<?php

$model = cash()->getModel(cashTransaction::class);
try {
    $model->exec('update cash_category set is_profit = 0 where is_profit is null');
} catch (Exception $exception) {
}
