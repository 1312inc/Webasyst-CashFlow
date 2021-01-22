<?php

$model = cash()->getModel(cashTransaction::class);

try {
    $model->exec('select is_onbadge from cash_repeating_transaction limit 1');
} catch (Exception $exception) {
    $model->exec('alter table cash_repeating_transaction add is_onbadge tinyint default 0');
}

try {
    $model->exec('select is_onbadge from cash_transaction limit 1');
} catch (Exception $exception) {
    $model->exec('alter table cash_transaction add is_onbadge tinyint default 0');
}
