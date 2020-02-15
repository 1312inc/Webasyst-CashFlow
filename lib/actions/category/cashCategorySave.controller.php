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

        $saver = new cashCategorySaver();
        $category = $saver->saveFromArray($data);
        if ($category) {
            $categoryDto = cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category);
            $this->response = $categoryDto;
        } else {
            $this->errors[] = _w('Some error on category save');
        }
    }
}
