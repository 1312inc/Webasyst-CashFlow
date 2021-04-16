<?php

try {
    $model = cash()->getModel(cashTransaction::class);

    $model->exec(
        sprintf(
            'update cash_transaction set category_id = %s where category_id is null and amount > 0',
            cashCategoryFactory::NO_CATEGORY_INCOME_ID
        )
    );

    $model->exec(
        sprintf(
            'update cash_transaction set category_id = %s where category_id is null and amount < 0',
            cashCategoryFactory::NO_CATEGORY_EXPENSE_ID
        )
    );

    if ((new waAppSettingsModel())->get('cash', 'innodb')) {
        $model->exec("ALTER TABLE `cash_transaction` DROP FOREIGN KEY `cash_transaction_cash_category_id_fk`");
        $model->exec("ALTER TABLE `cash_transaction` CHANGE `category_id` `category_id` INT(11)  NOT NULL");
        $model->exec(
            "ALTER TABLE `cash_transaction` ADD CONSTRAINT `cash_transaction_cash_category_id_fk` FOREIGN KEY (`category_id`) REFERENCES `cash_category` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE"
        );
    }
} catch (Exception $exception) {

}
