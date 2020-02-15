<?php

$m = new waModel();
$db = require wa('cash')->getConfig()->getAppConfigPath('db');

$innodb = 0;
foreach ($db as $table => $info) {
    try {
        $m->exec(sprintf('alter table %s engine=innodb', $table));
        $innodb = 1;
    } catch (waException $ex) {
        waLog::log('mysql do not support InnoDb engine', 'cash/error.log');
        waLog::log($ex->getMessage(), 'cash/error.log');
    } finally {
        (new waAppSettingsModel())->set('cash', 'innodb', $innodb);
    }
}

if ($innodb) {
    try {
        $m->exec(
            'alter table cash_transaction
                    add constraint cash_transaction_cash_account_id_fk
                        foreign key (account_id) references cash_account (id)
                            on update cascade on delete cascade'
        );
        $m->exec(
            'alter table cash_transaction
                    add constraint cash_transaction_cash_category_id_fk
                        foreign key (category_id) references cash_category (id)
                            on update cascade on delete set null'
        );
        $m->exec(
            'alter table cash_transaction
                    add constraint cash_transaction_cash_repeating_transaction_id_fk
                        foreign key (repeating_id) references cash_repeating_transaction (id)
                            on update cascade on delete set null;'
        );
        $m->exec(
            'alter table cash_repeating_transaction
                    add constraint cash_repeating_transaction_cash_account_id_fk
                        foreign key (account_id) references cash_account (id)
                            on delete cascade'
        );
        $m->exec(
            'alter table cash_repeating_transaction
                    add constraint cash_repeating_transaction_cash_category_id_fk
                        foreign key (category_id) references cash_category (id)
                            on update cascade on delete set null'
        );
    } catch (waException $ex) {
        waLog::log('fail to add foreign keys', 'cash/error.log');
        waLog::log($ex->getMessage(), 'cash/error.log');
    }
} else {
    throw new waException('InnoDb engine is required');
}
