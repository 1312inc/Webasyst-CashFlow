<?php

/**
 * Class cashApiCategoryUpdateHandler
 */
class cashApiCategoryUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategoryUpdateRequest $request
     *
     * @return cashApiCategoryResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashCategoryRepository $repository */
        $repository = cash()->getEntityRepository(cashCategory::class);

        /** @var cashCategory|null $category */
        $category = $repository->findById($request->getId());
        if (!$category) {
            throw new kmwaNotFoundException(_w('No category'));
        }

        if ($category->isSystem()) {
            throw new kmwaForbiddenException(_w('You can`t do anything with system categories'));
        }

        if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
            throw new kmwaForbiddenException(_w('You have no access to this category'));
        }

        $saver = new cashCategorySaver();
        if ($saver->saveFromApi($category, $request)) {
            $saver->resort($category->getType());

            return cashApiCategoryResponseDto::fromCategory($category);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
