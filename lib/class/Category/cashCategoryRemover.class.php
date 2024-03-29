<?php

/**
 * Class cashCategoryRemover
 */
class cashCategoryRemover
{
    /**
     * @var cashCategoryRepository
     */
    private $categoryRepository;

    /**
     * @var string
     */
    private $error;

    /**
     * cashCategoryRemover constructor.
     *
     * @param cashCategoryRepository $categoryRepository
     */
    public function __construct(cashCategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param cashCategory $category
     *
     * @return bool
     * @throws waException
     */
    public function removeCategory(cashCategory $category): bool
    {
        if ($category->isSystem()) {
            $this->error = _w('You can`t do anything with system categories');

            return false;
        }

        /** @var cashTransactionModel $transactionModel */
        $transactionModel = cash()->getModel(cashTransaction::class);
        /** @var cashRepeatingTransactionModel $repeatingTransactionModel */
        $repeatingTransactionModel = cash()->getModel(cashRepeatingTransaction::class);

        $transactionModel->startTransaction();
        try {
            $transactionModel->archiveByCategoryId($category->getId());

            $transactionModel->changeCategoryId(
                $category->getId(),
                $category->isExpense()
                    ? $this->categoryRepository->findNoCategoryExpense()->getId()
                    : $this->categoryRepository->findNoCategoryIncome()->getId()
            );
            $repeatingTransactionModel->changeCategoryId(
                $category->getId(),
                $category->isExpense()
                    ? $this->categoryRepository->findNoCategoryExpense()->getId()
                    : $this->categoryRepository->findNoCategoryIncome()->getId()
            );

            cash()->getEntityPersister()->delete($category);

            $transactionModel->commit();
        } catch (Exception $ex) {
            $transactionModel->rollback();

            $this->error = _w('Error while deleting category');

            cash()->getLogger()->error($this->error, $ex);

            return false;
        }

        return true;
    }

    /**
     * @return string
     */
    public function getError(): string
    {
        return $this->error;
    }
}
