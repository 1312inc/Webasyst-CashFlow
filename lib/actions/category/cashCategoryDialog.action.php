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
            if (!cash()->getContactRights()->isAdmin(wa()->getUser())) {
                throw new kmwaForbiddenException(_w('You can not create any category at all'));
            }

            $category = cash()->getEntityFactory(cashCategory::class)->createNew();
        } else {
            $category = cash()->getEntityRepository(cashCategory::class)->findById($id);
            kmwaAssert::instance($category, cashCategory::class);

            if (!cash()->getContactRights()->hasFullAccessToCategory(wa()->getUser(), $category)) {
                throw new kmwaForbiddenException(_w('You have no access to this category'));
            }
        }

        $this->view->assign(
            [
                'category' => cashDtoFromEntityFactory::fromEntity(cashCategoryDto::class, $category),
            ]
        );
    }
}
