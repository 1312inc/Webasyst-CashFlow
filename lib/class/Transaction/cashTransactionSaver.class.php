<?php

/**
 * Class cashTransactionSaver
 */
class cashTransactionSaver extends cashEntitySaver
{
    const MAX_AMOUNT = 1000000000000.;
    const MIN_AMOUNT = -1000000000000.;

    /**
     * @var cashTransaction[]
     */
    private $toPrsist = [];

    /**
     * @param cashTransaction              $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|array<cashTransaction>
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
            $transferTransaction = $this->createTransfer($transaction, $params);
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
        } elseif($params->categoryType === cashCategory::TYPE_TRANSFER) {
            if ($transaction->getAmount() < 0) {
                $data['amount'] = -abs($data['amount']);
            } else {
                $data['amount'] = abs($data['amount']);
            }
        }

        $data = $this->addCategoryId($data);

        if (!empty($data['external_source'])) {
            // todo: event для плагинов
            switch ($data['external_source']) {
                case 'shop':
                    $integration = new cashShopIntegration();
                    if ($integration->shopExists()) {
                        if (empty($data['is_self_destruct_when_due'])) {
                            try {
                                $order = new shopOrder($data['external_id']);
                            } catch (waException $exception) {
                                $this->error = 'No order with such id';

                                return false;
                            }

                            $data['external_hash'] = $integration->getTransactionFactory()->generateExternalHash(
                                $order,
                                cashShopTransactionFactory::CUSTOM . time(),
                                $data['amount']
                            );
                            $data['external_id'] = $order->getId();
                            $data['external_data'] = array_merge($data['external_data'] ?? [], ['id' => $order->getId()]
                            );
                        } else {
                            $data['external_hash'] = cashShopTransactionFactory::HASH_FORECAST;
                        }
                    }
            }
        } else {
            $data['external_source'] = null;
            $data['external_hash'] = null;
            $data['external_id'] = null;
            $data['external_data'] = null;
        }

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
            throw new kmwaLogicException(_w('No incoming transfer amount specified'));
        }

        if (!isset($params->transfer['account_id'])) {
            throw new kmwaLogicException('No incoming transfer account selected');
        }

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($params->transfer['account_id']);
        kmwaAssert::instance($account, cashAccount::class);

        /** @var cashCategoryRepository $categoryRepository */
        $categoryRepository = cash()->getEntityRepository(cashCategory::class);
        $category = $categoryRepository->findTransferCategory();

        $amount = cashHelper::parseFloat($params->transfer['incoming_amount']);
        $transferTransaction = clone $transaction;
        $transferTransaction
            ->setId(null)
            ->setAmount($amount)
            ->setCategory($category)
            ->setAccount($account);

        $transaction
            ->setAmount(-abs($transaction->getAmount()))
            ->setLinkedTransaction($transferTransaction)
            ->setCategory($category);

        if (empty($transaction->getDescription())) {
            $transaction->setDescription(sprintf_wp('Withdrawal to %s', $transaction->getAccount()->getName()));
            $transferTransaction->setDescription(sprintf_wp('Receipt from %s', $account->getName()));
        }

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
            unset($this->toPrsist);
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
        if (empty($data['date'])) {
            $this->error = _w('No transaction date specified');

            return false;
        }

        if (empty($data['amount'])) {
            $this->error = _w('No amount specified');

            return false;
        }

        $data['amount'] = cashHelper::parseFloat($data['amount']);

        if ($data['amount'] > self::MAX_AMOUNT) {
            $this->error = _w('Come on, all of the world\'s money is less than the amount entered!');

            return false;
        }

        if ($data['amount'] < self::MIN_AMOUNT) {
            $this->error = _w('Come on, all of the world\'s money is less than the amount entered!');

            return false;
        }

        if (empty($data['account_id'])) {
            $this->error = _w('No account selected');

            return false;
        }

        if (empty($data['contractor_contact_id'])) {
            $data['contractor_contact_id'] = null;
        }

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
     * @return cashTransaction[]
     */
    public function getToPersist(): array
    {
        return $this->toPrsist;
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
                if ($category->isIncome() && $data['amount'] < 0) {
                    $data['amount'] = abs($data['amount']);
                }
            } elseif (!in_array($data['category_id'], cashCategoryFactory::getSystemIds())) {
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
            throw new kmwaLogicException('No incoming transfer account selected');
        }

        $secondTransaction->setAccountId($transferData['account_id']);

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($transferData['account_id']);
        kmwaAssert::instance($account, cashAccount::class);

        if ($transaction->getAccount()->getCurrency() !== $account->getCurrency()) {
            throw new kmwaNotImplementedException('No currency exchange logic defined');
        }

        cash()->getEntityPersister()->save($secondTransaction);

        return $secondTransaction;
    }
}
