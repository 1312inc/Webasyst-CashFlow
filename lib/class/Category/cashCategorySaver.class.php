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
            if ($category->getType() && $createRequest->getType() !== $category->getType()) {
                /** для перемещения существующих категорий */
                $category_model = cash()->getModel(cashCategory::class);
                $category_model->updateByField('category_parent_id', $category->getId(), [
                    'type' => $createRequest->getType(),
                    'is_profit' => ($createRequest->getType() === cashShopTransactionFactory::INCOME ? 0 : $createRequest->getIsProfit())
                ]);
            }
            $category->setSort($createRequest->getSort())
                ->setName($createRequest->getName())
                ->setType($createRequest->getType())
                ->setGlyph($createRequest->getGlyph())
                ->setIsProfit(($createRequest->getType() === cashShopTransactionFactory::INCOME ? 0 : $createRequest->getIsProfit()))
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

    public function validate(array &$data): bool
    {
        $data['name'] = trim($data['name']);
        if ($data['name'] === '') {
            $this->error = _w('Empty category name');

            return false;
        }

        return true;
    }

    public function sort(array $order): bool
    {
        $categories = array_column(
            cash()->getModel(cashCategory::class)
                ->select('id')
                ->where('id in (i:ids)', ['ids' => $order])
                ->fetchAll(),
            'id'
        );

        $i = 0;
        foreach ($order as $categoryId) {
            if (!in_array($categoryId, $categories)) {
                continue;
            }

            cash()->getModel(cashCategory::class)
                ->updateById($categoryId, ['sort' => $i++]);
        }

        return true;
    }

    public function resort($type): bool
    {
        $categories = cash()->getModel(cashCategory::class)
            ->getAllSorted($type, 'category_parent_id', 2);
        $sort = [];
        $i = 0;
        foreach ($categories as $cats) {
            foreach ($cats as $cat) {
                if (isset($sort[$cat['id']])) {
                    continue 2;
                }

                $sort[$cat['id']] = $i++;
                if (isset($categories[$cat['id']])) {
                    foreach ($categories[$cat['id']] as $c) {
                        $sort[$c['id']] = $i++;
                    }
                }
            }
        }

        // system categories hack
//        $sort[cashCategoryFactory::TRANSFER_CATEGORY_ID] = 1312;
//        $sort[cashCategoryFactory::NO_CATEGORY_EXPENSE_ID] = -1312;
//        $sort[cashCategoryFactory::NO_CATEGORY_INCOME_ID] = -1312;

        foreach ($sort as $id => $pos) {
            cash()->getModel(cashCategory::class)
                ->updateById($id, ['sort' => $pos]);
        }

        return false;
    }
}
