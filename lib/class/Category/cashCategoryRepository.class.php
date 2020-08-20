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
    public function findAllByType($type): array
    {
        return $this->generateWithData($this->getModel()->getByType($type), true);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllActive(): array
    {
        return $this->generateWithData($this->getModel()->getAllActive(), true);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllIncome(): array
    {
        return $this->findAllByType(cashCategory::TYPE_INCOME);
    }

    /**
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllExpense(): array
    {
        return $this->findAllByType(cashCategory::TYPE_EXPENSE);
    }

    /**
     * @return cashCategory
     * @throws waException
     */
    public function findTransferCategory(): cashCategory
    {
        $transfers = $this->findById(cashCategoryFactory::TRANSFER_CATEGORY_ID);
        if (!$transfers instanceof cashCategory) {
            $transfers = cash()->getEntityFactory(cashCategory::class)->createNewTransferCategory();
            cash()->getEntityPersister()->insert($transfers);
        }

        return $transfers;
    }
}
