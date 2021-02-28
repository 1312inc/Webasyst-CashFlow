<?php

cash()->getLogger()->log('Cash install start', 'install');

$m = new waModel();
$db = require wa('cash')->getConfig()->getAppConfigPath('db');

$innodb = 0;
try {
    foreach ($db as $table => $info) {
        $m->exec(sprintf('alter table %s engine=innodb', $table));
        $innodb = 1;
    }
} catch (Exception $ex) {
    cash()->getLogger()->error('mysql do not support InnoDb engine', $ex, 'install');
} finally {
    (new waAppSettingsModel())->set('cash', 'innodb', $innodb);
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
                            on update cascade on delete restrict '
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
                            on update cascade on delete restrict'
        );

        cash()->getLogger()->log('Foreign key added', 'install');

        $installing = new cashFixtures();

        cash()->getLogger()->log('Start: add accounts and categories', 'install');
        $installing->createAccountsAndCategories();
        cash()->getLogger()->log('Done: add accounts and categories', 'install');

        cash()->getLogger()->log('Start: create demo account', 'install');
        $installing->createDemo();
        cash()->getLogger()->log('Done: create demo account', 'install');
    } catch (waException $ex) {
        cash()->getLogger()->error('Fail on add foreign keys or account/categories creation', $ex);

        throw $ex;
    }
} else {
    throw new waException('InnoDb engine is required');
}
