<?php

/**
 * Class cashApiCategoryCreateHandler
 */
class cashApiCategoryCreateHandler implements cashApiHandlerInterface
{
    /**
     * @param cashApiCategoryCreateRequest $request
     *
     * @return cashApiCategoryResponseDto
     * @throws kmwaForbiddenException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function handle($request)
    {
        if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
            throw new kmwaForbiddenException(_w('You can not create any category at all'));
        }

        /** @var cashCategoryFactory $repository */
        $factory = cash()->getEntityFactory(cashCategory::class);
        $category = $factory->createNew();

        $saver = new cashCategorySaver();
        $data = (array) $request;

        if ($saver->saveFromArray($category, $data)) {
            return cashApiCategoryResponseDto::fromCategory($category);
        }

        throw new kmwaRuntimeException($saver->getError());
    }
}
