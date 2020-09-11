<?php

/**
 * Class cashApiCategoryUpdateHandler
 */
class cashApiCategoryUpdateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategoryUpdateRequest $request
     *
     * @return array|cashApiCategoryResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaNotFoundException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        /** @var cashCategoryRepository $repository */
        $repository = cash()->getEntityRepository(cashCategory::class);
        $category = $repository->findById($request->id);
        if (!$category) {
            throw new kmwaNotFoundException(_w('No category'));
        }

        if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
            throw new kmwaForbiddenException(_w('You have no access to this category'));
        }

        $saver = new cashCategorySaver();
        $data = (array) $request;
        if ($saver->saveFromArray($category, $data)) {
            return cashApiCategoryResponseDto::fromCategory($category);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
