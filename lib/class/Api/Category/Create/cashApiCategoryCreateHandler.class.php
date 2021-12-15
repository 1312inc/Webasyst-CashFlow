<?php

final class cashApiCategoryCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategoryCreateRequest $request
     *
     * @return cashApiCategoryResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request): cashApiCategoryResponseDto
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can not create any category at all'));
        }

        /** @var cashCategoryFactory $repository */
        $factory = cash()->getEntityFactory(cashCategory::class);
        $category = $factory->createNew();

        $saver = new cashCategorySaver();

        if ($saver->saveFromApi($category, $request)) {
            $saver->resort($category->getType());

            return cashApiCategoryResponseDto::fromCategory($category);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
