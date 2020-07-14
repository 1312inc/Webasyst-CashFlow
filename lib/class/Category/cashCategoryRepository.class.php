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
     * @return cashCategory
     * @throws waException
     */
    public function findTransferCategory()
    {
        $transfers = $this->findById(cashCategoryFactory::TRANSFER_CATEGORY_ID);
        if (!$transfers instanceof cashCategory) {
            $data = [
                'id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
                'type' => cashCategory::TYPE_TRANSFER,
                'color' => cashColorStorage::TRANSFER_CATEGORY_COLOR,
                'name' => _w('Transfers'),
                'create_datetime' => date('Y-m-d H:i:s'),
            ];
            $this->getModel()->insert($data);
            $transfers = cash()->getHydrator()->hydrate(
                cash()->getEntityFactory(cashCategory::class)->createNew(),
                $data
            );
        }

        return $transfers;
    }
}
