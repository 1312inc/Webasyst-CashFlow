<?php

/**
 * Class cashCategorySaver
 */
class cashCategorySaver extends cashEntitySaver
{
    /**
     * @param array $data
     *
     * @return bool|cashCategory
     */
    public function save(array $data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            /** @var cashCategoryRepository $rep */
            $rep = cash()->getEntityRepository(cashCategory::class);
            /** @var cashCategoryModel $model */
            $model = cash()->getModel(cashCategory::class);

            /** @var cashCategory $category */
            if (!empty($data['id'])) {
                $category = $rep->findById($data['id']);
            } else {
                $category = cash()->getEntityFactory(cashCategory::class)->createNew();
            }
            unset($data['id']);
            kmwaAssert::instance($category, cashCategory::class);

            if (isset($data['slug'])) {
                $data['slug'] = $model->getUniqueSlug($category->getSlug());
            }

            if (empty($category->getColor())) {
                $colors = cashColorStorage::getColorsByType($category->getType());
                $category->setColor($colors(array_rand($colors)));
            }

            cash()->getHydrator()->hydrate($category, $data);
            cash()->getEntityPersister()->save($category);

            return $category;
        } catch (Exception $ex) {
            $this->error = $ex->getMessage();
        }

        return false;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array $data)
    {
        if (empty($data['name'])) {
            $this->error = _w('No category name');

            return false;
        }

        return true;
    }
}
