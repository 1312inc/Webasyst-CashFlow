<?php

/**
 * Class cashCategoryDialogAction
 */
class cashCategoryDialogAction extends cashViewAction
{
    /**
     * @param null $params
     *
     * @return mixed|void
     * @throws kmwaAssertException
     * @throws waException
     */
    public function runAction($params = null)
    {
        $id = waRequest::get('category_id', 0, waRequest::TYPE_INT);
        if (empty($id)) {
            $category = cash()->getEntityFactory(cashCategory::class)->createNew();
        } else {
            $category = cash()->getEntityRepository(cashCategory::class)->findById($id);
            kmwaAssert::instance($category, cashCategory::class);
        }

        $this->view->assign(
            [
                'category' => cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category),
            ]
        );
    }
}
