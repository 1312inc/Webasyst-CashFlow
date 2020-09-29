<?php

cash()->getEntityPersister()->insert(
    (new cashAccount())
        ->setName(wa()->accountName())
        ->setCurrency(wa()->getLocale() === 'en_US' ? 'USD' : 'RUB')
        ->setIcon('star')
        ->setCustomerContactId(wa()->getUser()->getId())
);

$fixtures = [
    cashCategory::TYPE_INCOME => [
        _w('Sales') => '#00dd00',
        _w('Investment') => '#001100',
    ],
    cashCategory::TYPE_EXPENSE => [
        _w('Salary') => '#dd0000',
        _w('Tax') => '#110000',
    ],
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
