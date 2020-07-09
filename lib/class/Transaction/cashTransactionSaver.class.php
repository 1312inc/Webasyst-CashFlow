<?php

/**
 * Class cashTransactionSaver
 */
class cashTransactionSaver extends cashEntitySaver
{
    /**
     * @var cashTransaction[]
     */
    private $toPrsist = [];

    /**
     * @param cashTransaction              $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashTransaction
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws waException
     */
    public function saveFromArray($transaction, array $data, cashTransactionSaveParamsDto $params)
    {
        $toPersist = [];
        if ($this->populateFromArray($transaction, $data, $params)) {
            $toPersist[] = $transaction;
        }

        if ($params->repeating) {
            $transferTransaction = $transaction-$this->createTransfer($transaction, $params);
            if ($transferTransaction) {
                $toPersist[] = $transferTransaction;
            }
        }

        return $this->persistTransactions($toPersist);
    }

    /**
     * @param cashTransaction              $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashTransaction
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws waException
     */
    public function populateFromArray(cashTransaction $transaction, array $data, cashTransactionSaveParamsDto $params)
    {
        if (!$this->validate($data)) {
            return false;
        }

        if ($params->categoryType === cashCategory::TYPE_EXPENSE) {
            $data['amount'] = -abs($data['amount']);
        } elseif ($params->categoryType === cashCategory::TYPE_INCOME) {
            $data['amount'] = abs($data['amount']);
        }

        $data = $this->addCategoryId($data);

        cash()->getHydrator()->hydrate($transaction, $data);

        return $transaction;
    }

    /**
     * @param cashTransaction              $transaction
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashTransaction
     * @throws kmwaAssertException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws waException
     */
    public function createTransfer(cashTransaction $transaction, cashTransactionSaveParamsDto $params)
    {
        if (!$params->transfer) {
            return false;
        }

        if (empty($params->transfer['incoming_amount'])) {
            throw new kmwaLogicException(_w('No transfer amount'));
        }

        $amount = (float)$params->transfer['incoming_amount'];

        $transferTransaction = clone $transaction;
        $transferTransaction
            ->setId(null)
            ->setAmount($amount);

        $params->transfer['category_id'] = $transaction->getCategoryId();
        if (!isset($params->transfer['account_id'])) {
            throw new kmwaLogicException('No account for transfer to');
        }

        if ($params->transfer['category_id']) {
            /** @var cashCategory $category */
            $category = cash()->getEntityRepository(cashCategory::class)->findById($params->transfer['category_id']);
            kmwaAssert::instance($category, cashCategory::class);
        } else {
            $category = cash()->getEntityFactory(cashCategory::class)->createNewNoCategory();
        }
        $transferTransaction->setCategory($category);

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($params->transfer['account_id']);
        kmwaAssert::instance($account, cashAccount::class);
        $transferTransaction->setAccount($account);

        $transaction
            ->setAmount(-abs($transaction->getAmount()))
            ->setLinkedTransaction($transferTransaction)
        ;

        return $transferTransaction;
    }

    /**
     * @param cashTransaction[] $transactions
     *
     * @return array|bool
     * @throws waException
     * @throws Exception
     */
    public function persistTransactions(array $transactions = [])
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $model->startTransaction();

        if ($transactions) {
            $this->toPrsist = $transactions;
        }

        try {
            $saved = [];
            foreach ($this->toPrsist as $transaction) {
                if (!$transaction instanceof cashTransaction) {
                    continue;
                }
                cash()->getEntityPersister()->save($transaction);
                $saved[] = $transaction;
            }

            $model->commit();
            $this->toPrsist = [];

            return $saved;
        } catch (Exception $ex) {
            $model->rollback();

            throw $ex;
        }
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array &$data)
    {
        if (empty($data['amount'])) {
            $this->error = _w('No amount');

            return false;
        }

        if (empty($data['account_id'])) {
            $this->error = _w('No account');

            return false;
        }

        $data['amount'] = cashHelper::parseFloat($data['amount']);

        return true;
    }

    /**
     * @param cashTransaction $transaction
     */
    public function addToPersist(cashTransaction $transaction)
    {
        $this->toPrsist[spl_object_hash($transaction)] = $transaction;
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
        unset($data['category']);

        if (isset($data['category_id'])) {
            if ($data['category_id'] > 0) {
                /** @var cashCategory $category */
                $category = cash()->getEntityRepository(cashCategory::class)->findById($data['category_id']);
                kmwaAssert::instance($category, cashCategory::class);
                if ($category->isExpense() && $data['amount'] > 0) {
                    $data['amount'] = -$data['amount'];
                }
            } else {
                $data['category_id'] = null;
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
