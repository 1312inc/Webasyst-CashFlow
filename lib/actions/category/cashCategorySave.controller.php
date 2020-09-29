<?php

/**
 * Class cashCategorySaveController
 */
class cashCategorySaveController extends cashJsonController
{
    /**
     * @throws Exception
     */
    public function execute()
    {
        $data = waRequest::post('category', [], waRequest::TYPE_ARRAY);

        if (!empty($data['id'])) {
            $category = cash()->getEntityRepository(cashCategory::class)->findById($data['id']);
            kmwaAssert::instance($category, cashCategory::class);

            if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
                throw new kmwaForbiddenException(_w('You are not allowed to access this category'));
            }
        } else {
            if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
                throw new kmwaForbiddenException(_w('You are not allowed to create new categories'));
            }

            $category = cash()->getEntityFactory(cashCategory::class)->createNew();
        }

        if ($category->isSystem()) {
            throw new kmwaRuntimeException(_w('You are not allowed to do anything with system categories'));
        }

        $saver = new cashCategorySaver();
        $category = $saver->saveFromArray($category, $data);
        if ($category) {
            $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category);
            $this->response = $categoryDto;
        } else {
            $this->errors[] = _w('An error occurred while saving the category');
        }
    }
}
