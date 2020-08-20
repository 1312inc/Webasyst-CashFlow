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

        $remover = new cashCategoryRemover(cash()->getEntityFactory(cashCategory::class));
        if (!$remover->removeCategory($category)) {
            $this->setError($remover->getError());
        }
    }
}
