<?php

if (!cash()->getEntityRepository(cashCategory::class)->findById(cashCategoryFactory::NO_CATEGORY_INCOME_ID)) {
    $incomeNoCategory = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryIncome();
    $data = cash()->getHydrator()->extract($incomeNoCategory);
    cash()->getModel(cashCategory::class)->insert(array_merge($data, ['create_datetime' => date('Y-m-d H:i:s')]));

    cash()->getModel(cashTransaction::class)->exec(
        sprintf(
            'update cash_transaction set category_id = %s where category_id is null and amount > 0',
            cashCategoryFactory::NO_CATEGORY_INCOME_ID
        )
    );
}

if (!cash()->getEntityRepository(cashCategory::class)->findById(cashCategoryFactory::NO_CATEGORY_EXPENSE_ID)) {
    $expenseNoCategory = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryExpense();
    $data = cash()->getHydrator()->extract($expenseNoCategory);
    cash()->getModel(cashCategory::class)->insert(array_merge($data, ['create_datetime' => date('Y-m-d H:i:s')]));

    cash()->getModel(cashTransaction::class)->exec(
        sprintf(
            'update cash_transaction set category_id = %s where category_id is null and amount < 0',
            cashCategoryFactory::NO_CATEGORY_EXPENSE_ID
        )
    );
}
