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
        } else {
            $category = cash()->getEntityFactory(cashCategory::class)->createNew();
        }

        if ($category->isSystem()) {
            throw new kmwaRuntimeException(_w('You can`t do anything with system categories'));
        }

        $saver = new cashCategorySaver();
        $category = $saver->saveFromArray($category, $data);
        if ($category) {
            $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category);
            $this->response = $categoryDto;
        } else {
            $this->errors[] = _w('Some error on category save');
        }
    }
}
