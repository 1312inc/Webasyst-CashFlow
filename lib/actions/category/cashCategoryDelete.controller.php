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
            $this->errors[] = $ex->getMessage();
        }
    }
}
