<?php

/**
 * Class cashTransactionBulkMoveController
 */
class cashTransactionBulkMoveController extends cashJsonController
{
    public function execute()
    {
        $moveData = waRequest::post('move', [], waRequest::TYPE_ARRAY_TRIM);
        if (!$moveData) {
            $this->setError('No data');

            return;
        }
        $ids = explode(',', $moveData['ids']);
        $transactions = cash()->getEntityRepository(cashTransaction::class)->findById($ids);
        $updateData = [];

        $saver = new cashTransactionSaver();

        $account = null;
        if (!empty($moveData['account_id'])) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById($moveData['account_id']);
            kmwaAssert::instance($account, cashAccount::class);

            $updateData['account_id'] = $account->getId();
        }

        if (isset($moveData['category_id'])) {
            if ($moveData['category_id']) {
                /** @var cashAccount $category */
                $category = cash()->getEntityRepository(cashCategory::class)->findById($moveData['category_id']);
                kmwaAssert::instance($category, cashCategory::class);

                $updateData['category_id'] = $category->getId();
            } else {
                $updateData['category_id'] = null;
            }
        }

        $fields = cash()->getModel(cashTransaction::class)->getMetadata();
        foreach ($transactions as $transaction) {
            $transactionData = cash()->getHydrator()->extract($transaction, [], $fields);
            $saver->saveFromArray($transaction, array_merge($transactionData, $updateData));
        }
    }
}
