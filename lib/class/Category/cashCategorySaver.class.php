<?php

/**
 * Class cashCategorySaver
 */
class cashCategorySaver extends cashEntitySaver
{
    /**
     * @param cashCategory                 $category
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashCategory
     */
    public function saveFromArray($category, array $data, cashTransactionSaveParamsDto $params = null)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            unset($data['id']);
            kmwaAssert::instance($category, cashCategory::class);

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
     * @return bool|cashCategory
     *
     * @throws waException
     */
    public function saveFromApi(
        cashCategory $category,
        cashApiCategoryCreateRequest $createRequest,
        ?cashTransactionSaveParamsDto $params = null
    ) {
        if ($createRequest->getParentCategoryId()) {
            /** @var cashCategory $parentCategory */
            $parentCategory = cash()->getEntityRepository(cashCategory::class)
                ->findById($createRequest->getParentCategoryId());

            if (!$parentCategory) {
                $this->error = sprintf('No parent category with id %s', $createRequest->getParentCategoryId());

                return false;
            }

            if ($parentCategory->getCategoryParentId()) {
                $this->error = 'There is already parent for passed parent. One level is allowed';

                return false;
            }
        }

        try {
            $category->setSort($createRequest->getSort())
                ->setName($createRequest->getName())
                ->setType($createRequest->getType())
                ->setGlyph($createRequest->getGlyph())
                ->setIsProfit($createRequest->getIsProfit())
                ->setColor($createRequest->getColor())
                ->setCategoryParentId($createRequest->getParentCategoryId());

            if (isset($parentCategory)) {
                $category->setType($parentCategory->getType());
            }

            if (empty($category->getColor())) {
                $colors = cashColorStorage::getColorsByType($category->getType());
                $category->setColor($colors[array_rand($colors)]);
            }

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
    public function validate(array &$data)
    {
        $data['name'] = trim($data['name']);
        if ($data['name'] === '') {
            $this->error = _w('Empty category name');

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
                array_column(
                    cash()->getModel(cashCategory::class)
                        ->select('*')
                        ->where('id in (i:ids)', ['ids' => $order])
                        ->fetchAll(),
                    'id'
                )
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
