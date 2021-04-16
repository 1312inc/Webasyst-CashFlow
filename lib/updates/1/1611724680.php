<?php

$model = cash()->getModel(cashTransaction::class);

try {
    $model->exec('select is_profit from cash_category limit 1');
} catch (Exception $exception) {
    $model->exec('alter table cash_category add is_profit tinyint default 0');
}
