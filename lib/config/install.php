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
        _w('Sales') => '#94fa4e',
        _w('Investment') => '#78fa7a',
        _w('Loan') => '#78faa2',
        _w('Cashback') => '#77fbfd',
        _w('Unexpected profit') => '#81cafa',
    ], true),
    cashCategory::TYPE_EXPENSE => array_reverse([
        _w('Salary') => '#e9382a',
        _w('Purchase') => '#d2483e',
        _w('Marketing') => '#d53964',
        _w('Delivery') => '#de6c92',
        _w('Rent') => '#eebecf',
        _w('Errand') => '#f7cebf',
        _w('Loan payout') => '#f9dea2',
        _w('Commission') => '#f2ab63',
        _w('Dividend') => '#e58231',
        _w('Refund') => '#b75822',
        _w('Tax') => '#a72e26',
        _w('Unexpected loss') => '#f7cfd3',
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
