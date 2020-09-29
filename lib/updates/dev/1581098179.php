<?php

$m = new waModel();

try {
    $m->exec('select * from cash_import');
} catch (Exception $ex) {
    $m->exec(
        <<<SQL
create table cash_import
(
	id int auto_increment,
	filename varchar(255) null,
	settings text null,
	params text null,
	create_datetime datetime not null,
	update_datetime datetime null,
	constraint cash_import_pk
		primary key (id)
)
SQL
    );
}
