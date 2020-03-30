<?php

/**
 * Class cashCategorySaver
 */
class cashCategorySaver extends cashEntitySaver
{
    /**
     * @param cashCategory $category
     * @param array        $data
     * @param array        $params
     *
     * @return bool|cashCategory
     */
    public function saveFromArray($category, array $data, array $params = [])
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            /** @var cashCategoryModel $model */
            $model = cash()->getModel(cashCategory::class);

            unset($data['id']);
            kmwaAssert::instance($category, cashCategory::class);

            if (isset($data['slug'])) {
                $data['slug'] = $model->getUniqueSlug($category->getSlug());
            }

            if (empty($category->getColor())) {
                $colors = cashColorStorage::getColorsByType($category->getType());
                $category->setColor($colors[array_rand($colors)]);
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

    /**
     * @param array $order
     *
     * @return bool
     */
    public function sort(array $order)
    {
        try {
            /** @var cashCategoryRepository $rep */
            $rep = cash()->getEntityRepository(cashCategory::class);
            /** @var cashCategory[] $categories */
            $categories = $rep->findById(
                cash()->getModel(cashCategory::class)
                    ->select('*')
                    ->where('id in (i:ids)', ['ids' => $order]),
                'id'
            );
            $i = 0;
            foreach ($order as $categoryId) {
                if (!isset($categories[$categoryId])) {
                    continue;
                }

                $categories[$categoryId]->setSort($i++);
                cash()->getEntityPersister()->save($categories[$categoryId]);
            }

            return true;
        } catch (Exception $exception) {
            $this->error = $exception->getMessage();
        }

        return false;
    }
}
