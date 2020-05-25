<?php

/**
 * Class cashCheckinFactory
 */
class cashCategoryFactory extends cashBaseFactory
{
    const NO_CATEGORY_EXPENSE_ID = -1;
    const NO_CATEGORY_INCOME_ID = -2;

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNew()
    {
        return (new cashCategory)
            ->setCreateDatetime(date('Y-m-d H:i:s'));
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategory()
    {
        return $this->createNew()->setName(_w('No category'));
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategoryExpense()
    {
        return $this->createNew()
            ->setName(_w('No category'))
            ->setId(self::NO_CATEGORY_EXPENSE_ID);
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategoryIncome()
    {
        return $this->createNew()
            ->setName(_w('No category'))
            ->setId(self::NO_CATEGORY_INCOME_ID);
    }
}
