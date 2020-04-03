<?php

/**
 * Class cashCategoryRepository
 *
 * @method cashCategoryModel getModel()
 */
class cashCategoryRepository extends cashBaseRepository
{
    protected $entity = cashCategory::class;

    /**
     * @param $type
     *
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllByType($type)
    {
        return $this->generateWithData($this->getModel()->getByType($type), true);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllActive()
    {
        return $this->generateWithData($this->getModel()->getAllActive(), true);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllIncome()
    {
        return $this->findAllByType(cashCategory::TYPE_INCOME);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllExpense()
    {
        return $this->findAllByType(cashCategory::TYPE_EXPENSE);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllTransfer()
    {
        return $this->findAllByType(cashCategory::TYPE_TRANSFER);
    }
}
