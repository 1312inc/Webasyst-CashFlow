<?php

$transfers = cash()->getEntityRepository(cashCategory::class)->findById(cashCategoryFactory::TRANSFER_CATEGORY_ID);
if (!$transfers) {
    cash()->getModel(cashCategory::class)->insert(
        [
            'id' => cashCategoryFactory::TRANSFER_CATEGORY_ID,
            'type' => cashCategory::TYPE_TRANSFER,
            'color' => cashColorStorage::TRANSFER_CATEGORY_COLOR,
            'name' => _w('Transfers'),
            'create_datetime' => date('Y-m-d H:i:s'),
        ]
    );
}
