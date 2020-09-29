<?php

$m = new waModel();

try {
    $m->exec('select * from cash_repeating_transaction');
} catch (Exception $ex) {
    $sql = <<<SQL
create table cash_repeating_transaction
(
    id                int auto_increment
        primary key,
    create_contact_id        int                       not null,
    enabled                  smallint    default 1     not null,
    repeating_frequency      int         default 1     not null,
    repeating_interval       varchar(20) default 'day' not null,
    repeating_conditions     text                      not null,
    repeating_end_conditions text                      null,
    repeating_occurrences    int         default 0     null,
    date                     date                      not null,
    account_id               int                       not null,
    category_id              int                       null,
    amount                   decimal(18, 4)            not null,
    description              text                      null,
    create_datetime          datetime                  not null,
    update_datetime          datetime                  null,
    constraint cash_repeating_transaction_cash_account_id_fk
        foreign key (account_id) references cash_account (id)
            on delete cascade,
    constraint cash_repeating_transaction_cash_category_id_fk
        foreign key (category_id) references cash_category (id)
            on update cascade on delete set null
);
SQL;
    $m->exec($sql);

    $sql = <<<SQL
alter table cash_transaction
	add constraint cash_transaction_cash_repeating_transaction_id_fk
		foreign key (repeating_id) references cash_repeating_transaction (id)
			on update cascade on delete set null;
SQL;
    $m->exec($sql);

}
