<?php

final class cashApiCategoryDeleteHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategoryDeleteRequest $request
     *
     * @return bool
     * @throws waException
     * @throws kmwaNotFoundException
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     */
    public function handle($request): bool
    {
        /** @var cashCategoryRepository $repository */
        $repository = cash()->getEntityRepository(cashCategory::class);

        /** @var cashCategory $category */
        $category = $repository->findById($request->getId());
        if (!$category) {
            throw new kmwaNotFoundException(_w('No category'));
        }

        if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
            throw new kmwaForbiddenException(_w('You have no access to this category'));
        }

        if ($category->isSystem()) {
            throw new kmwaRuntimeException(_w('You can`t do anything with system categories'), 400);
        }

        $remover = new cashCategoryRemover(cash()->getEntityRepository(cashCategory::class));
        if (!$remover->removeCategory($category)) {
            throw new kmwaRuntimeException($remover->getError());
        }
        cash()->getModel(cashCategory::class)->updateByField(
            'category_parent_id',
            $request->getId(),
            ['category_parent_id' => null]
        );

        return true;
    }
}
