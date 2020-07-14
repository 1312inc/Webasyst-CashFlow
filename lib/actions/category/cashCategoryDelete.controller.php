<?php

/**
 * Class cashCategoryDeleteController
 */
class cashCategoryDeleteController extends cashJsonController
{
    /**
     * @throws waException
     * @throws Exception
     */
    public function execute()
    {
        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById($this->getId());
        kmwaAssert::instance($category, cashCategory::class);

        if ($category->isSystem()) {
            throw new kmwaRuntimeException(_w('You can`t do anything with system categories'));
        }

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();
        try {
            $model->archiveByCategoryId($category->getId());
            if (!cash()->getEntityPersister()->delete($category)) {
                throw new kmwaRuntimeException(_w('Error while deleting category'));
            }

            $model->commit();
        } catch (Exception $ex) {
            $model->rollback();

            throw $ex;
        }
    }
}
