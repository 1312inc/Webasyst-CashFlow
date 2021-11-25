<?php

/**
 * Class cashCheckinFactory
 */
class cashCategoryFactory extends cashBaseFactory
{
    const NO_CATEGORY_EXPENSE_ID = -1;
    const NO_CATEGORY_INCOME_ID  = -2;
    const TRANSFER_CATEGORY_ID   = -1312;
    const NO_CATEGORY_COLOR      = '#dddddd';

    /**
     * @return int[]
     */
    public static function getSystemIds(): array
    {
        return [
            self::NO_CATEGORY_INCOME_ID,
            self::NO_CATEGORY_EXPENSE_ID,
            self::TRANSFER_CATEGORY_ID,
        ];
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNew(): cashCategory
    {
        return (new cashCategory)
            ->setCreateDatetime(date('Y-m-d H:i:s'))
            ->setIsProfit(false);
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategory(): cashCategory
    {
        return $this->createNew()->setName(_wd(cashConfig::APP_ID, 'No category'));
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategoryExpense(): cashCategory
    {
        return $this->createNewNoCategory()
            ->setColor(self::NO_CATEGORY_COLOR)
            ->setType(cashCategory::TYPE_EXPENSE)
            ->setId(self::NO_CATEGORY_EXPENSE_ID);
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewNoCategoryIncome(): cashCategory
    {
        return $this->createNewNoCategory()
            ->setColor(self::NO_CATEGORY_COLOR)
            ->setType(cashCategory::TYPE_INCOME)
            ->setId(self::NO_CATEGORY_INCOME_ID);
    }

    /**
     * @return cashCategory
     * @throws Exception
     */
    public function createNewTransferCategory(): cashCategory
    {
        return $this->createNew()
            ->setName(_wd(cashConfig::APP_ID, 'Transfers'))
            ->setColor(cashColorStorage::TRANSFER_CATEGORY_COLOR)
            ->setType(cashCategory::TYPE_TRANSFER)
            ->setId(self::TRANSFER_CATEGORY_ID);
    }
}
