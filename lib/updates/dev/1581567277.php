<?php

$m = new waModel();

try {
    $m->exec('select repeating_end_type from cash_repeating_transaction');
} catch (Exception $ex) {
    $sql = <<<SQL
alter table cash_repeating_transaction
	add repeating_end_type varchar(20) default 'never' null after repeating_conditions;
SQL;
    $m->exec($sql);
}
