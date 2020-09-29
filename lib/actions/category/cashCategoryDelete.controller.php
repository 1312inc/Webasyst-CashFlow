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

        if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
            throw new kmwaForbiddenException(_w('You are not allowed to access this category'));
        }

        $remover = new cashCategoryRemover(cash()->getEntityRepository(cashCategory::class));
        if (!$remover->removeCategory($category)) {
            $this->setError($remover->getError());
        }
    }
}
