<?php

$m = new waModel();
$db = require wa('cash')->getConfig()->getAppConfigPath('db');

$innodb = 0;
foreach ($db as $table => $info) {
    try {
        $m->exec(sprintf('alter table %s engine=innodb', $table));
        $innodb = 1;
    } catch (waException $ex) {
        cash()->getLogger()->error('mysql do not support InnoDb engine', $ex);
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
        cash()->getLogger()->error('fail to add foreign keys', $ex);
    }
} else {
    throw new waException('InnoDb engine is required');
}

cash()->getEntityPersister()->insert(
    (new cashAccount())
        ->setName(wa()->accountName())
        ->setCurrency(wa()->getLocale() === 'en_US' ? 'USD' : 'RUB')
        ->setIcon('star')
);

$fixtures = [
    cashCategory::TYPE_INCOME => array_reverse([
        _w('Sales') => '#00dd00',
        _w('Investment') => '#00dd00',
        _w('Loan') => '#00dd00',
        _w('Cashback') => '#00dd00',
        _w('Other') => '#33dd33',
    ], true),
    cashCategory::TYPE_EXPENSE => array_reverse([
        _w('Salary') => '#dd0000',
        _w('Purchase') => '#dd0000',
        _w('Marketing') => '#dd0000',
        _w('Rent') => '#dd0000',
        _w('Errand') => '#dd0000',
        _w('Loan payout') => '#dd0000',
        _w('Commission') => '#dd0000',
        _w('Dividend') => '#dd0000',
        _w('Tax') => '#dd0000',
        _w('Other') => '#dd3333',
    ], true),
];

foreach ($fixtures as $type => $categories) {
    foreach ($categories as $name => $color) {
        cash()->getEntityPersister()->insert(
            (new cashCategory())
                ->setType($type)
                ->setColor($color)
                ->setName($name)
        );
    }
}

cash()->getModel(cashCategory::class)->insert(
    [
        'id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
        'type' => cashCategory::TYPE_TRANSFER,
        'color' => cashColorStorage::TRANSFER_CATEGORY_COLOR,
        'name' => _w('Transfers'),
        'create_datetime' => date('Y-m-d H:i:s'),
    ]
);
