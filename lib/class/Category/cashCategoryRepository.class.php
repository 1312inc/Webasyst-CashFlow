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
     * @param                $type
     * @param waContact|null $contact
     *
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllByTypeForContact($type, waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->generateWithData($this->getModel()->getByTypeForContact($type, $contact), true);
    }

    /**
     * @param waContact|null $contact
     *
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllActiveForContact(waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->generateWithData($this->getModel()->getAllActiveForContact($contact), true);
    }

    /**
     * @param waContact|null $contact
     *
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllIncomeForContact(waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->findAllByTypeForContact(cashCategory::TYPE_INCOME, $contact);
    }

    /**
     * @param waContact|null $contact
     *
     * @return cashCategory[]
     * @throws waException
     */
    public function findAllExpense(waContact $contact = null): array
    {
        if (!$contact) {
            $contact = wa()->getUser();
        }

        return $this->findAllByTypeForContact(cashCategory::TYPE_EXPENSE, $contact);
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

    /**
     * @return cashCategory
     * @throws waException
     */
    public function findNoCategoryIncome(): cashCategory
    {
        $income = $this->findById(cashCategoryFactory::NO_CATEGORY_INCOME_ID);
        if (!$income instanceof cashCategory) {
            $income = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryIncome();
            cash()->getEntityPersister()->insert($income);
        }

        return $income;
    }

    /**
     * @return cashCategory
     * @throws waException
     */
    public function findNoCategoryExpense(): cashCategory
    {
        $expense = $this->findById(cashCategoryFactory::NO_CATEGORY_EXPENSE_ID);
        if (!$expense instanceof cashCategory) {
            $expense = cash()->getEntityFactory(cashCategory::class)->createNewNoCategoryExpense();
            cash()->getEntityPersister()->insert($expense);
        }

        return $expense;
    }
}
