<?php

return [
    cashEventStorage::TRANSACTION_PAGE_PREEXECUTE => [
        ['cashRepeatTransactionRepeater', 'afterTransactionPagePreExecute'],
    ],
    cashEventStorage::ON_COUNT => [
        ['cashShopIntegration', 'onCount']
    ]
];
