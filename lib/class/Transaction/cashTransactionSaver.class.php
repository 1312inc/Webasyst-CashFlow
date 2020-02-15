<?php

/**
 * Class cashTransactionSaver
 */
class cashTransactionSaver extends cashEntitySaver
{
    /**
     * @param array $data
     * @param array $params
     *
     * @return bool|cashTransaction
     * @throws waException
     */
    public function saveFromArray(array $data, array $params = [])
    {
        if (!$this->validate($data)) {
            return false;
        }

        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();

        try {
            /** @var cashTransaction $transaction */
            if (!empty($data['id'])) {
                $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($data['id']);
                kmwaAssert::instance($transaction, cashTransaction::class);
                unset($data['id']);
            } else {
                $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
            }

            $data = $this->addCategoryId($data);

            cash()->getHydrator()->hydrate($transaction, $data);

            if (!empty($params['transfer'])) {
                $this->transfer($transaction, $params['transfer']);
                if ($transaction->getAmount() > 0) {
                    $transaction->setAmount(-$transaction->getAmount());
                }
            }
            cash()->getEntityPersister()->save($transaction);

            $model->commit();

            return $transaction;
        } catch (Exception $ex) {
            $model->rollback();

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
        if (empty($data['amount'])) {
            $this->error = _w('No amount');

            return false;
        }

        if (empty($data['account_id'])) {
            $this->error = _w('No account');

            return false;
        }

        return true;
    }

    /**
     * @param array $data
     *
     * @return array
     * @throws kmwaAssertException
     * @throws waException
     */
    protected function addCategoryId(array $data)
    {
        if (!empty($data['category_id'])) {
            /** @var cashCategory $category */
            $category = cash()->getEntityRepository(cashCategory::class)->findById($data['category_id']);
            kmwaAssert::instance($category, cashCategory::class);
            if ($category->isExpense() && $data['amount'] > 0) {
                $data['amount'] = -$data['amount'];
            }
        } else {
            if (isset($data['category_type']) && $data['category_type'] === cashCategory::TYPE_EXPENSE && $data['amount'] > 0) {
                $data['amount'] = -$data['amount'];
            }
            unset($data['category_id'], $data['category_type']);
        }

        return $data;
    }

    /**
     * @param cashTransaction $transaction
     * @param array           $transferData
     *
     * @return cashTransaction
     * @throws kmwaAssertException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws waException
     */
    private function transfer(cashTransaction $transaction, array $transferData)
    {
        $amount = abs($transaction->getAmount());

        $secondTransaction = clone $transaction;
        $secondTransaction
            ->setId(null)
            ->setAmount($amount);

        if (!empty($transferData['category_id'])) {
            $secondTransaction->setCategoryId($transferData['category_id']);
        }
        if (empty($transferData['account_id'])) {
            throw new kmwaLogicException('No account for transfer to');
        }

        $secondTransaction->setAccountId($transferData['account_id']);

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($transferData['account_id']);
        kmwaAssert::instance($account, cashAccount::class);

        if ($transaction->getAccount()->getCurrency() !== $account->getCurrency()) {
            throw new kmwaNotImplementedException('No exchange logic');
        }

        cash()->getEntityPersister()->save($secondTransaction);

        return $secondTransaction;
    }
}
