<?php

/**
 * Class cashTransactionFactory
 */
class cashTransactionFactory extends cashBaseFactory
{
    /**
     * @return cashTransaction
     * @throws Exception
     */
    public function createNew()
    {
        return (new cashTransaction())
            ->setCreateDatetime(date('Y-m-d H:i:s'))
            ->setDatetime(date('Y-m-d H:i:s'))
            ->setCreateContactId(wa()->getUser()->getId())
            ->setDate(date('Y-m-d'));
    }

    /**
     * @param cashTransaction              $transaction
     * @param array                        $data
     * @param cashTransactionSaveParamsDto $params
     *
     * @return bool|cashTransaction
     * @throws ReflectionException
     * @throws cashValidateException
     * @throws kmwaAssertException
     * @throws kmwaLogicException
     * @throws kmwaNotImplementedException
     * @throws waException
     */
    public function createFromArray($transaction, array $data, cashTransactionSaveParamsDto $params)
    {
        $this->validate($data);

        if ($params->categoryType === cashCategory::TYPE_EXPENSE) {
            $data['amount'] = -abs($data['amount']);
        } elseif ($params->categoryType === cashCategory::TYPE_INCOME) {
            $data['amount'] = abs($data['amount']);
        }

        $data = $this->addCategoryId($data);

        cash()->getHydrator()->hydrate($transaction, $data);

        if ($params->transfer) {
            $transferTransaction = $this->transfer($transaction, $params->transfer);
            if ($transaction->getAmount() > 0) {
                $transaction->setAmount(-$transaction->getAmount());
            }

            $transaction->setLinkedTransaction($transferTransaction);
        }

        return $transaction;
    }

    /**
     * @param array $data
     *
     * @return bool
     */
    public function validate(array $data)
    {
        if (empty($data['amount'])) {
            throw new cashValidateException(_w('No amount specified'));
        }

        if (empty($data['account_id'])) {
            throw new cashValidateException(_w('No account selected'));        }

        $data['amount'] = cashHelper::parseFloat($data['amount']);

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
        if (array_key_exists('category_id', $data)) {
            if ($data['category_id']) {
                /** @var cashCategory $category */
                $category = cash()->getEntityRepository(cashCategory::class)->findById($data['category_id']);
                kmwaAssert::instance($category, cashCategory::class);
                if ($category->isExpense() && $data['amount'] > 0) {
                    $data['amount'] = -$data['amount'];
                }
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
            throw new kmwaLogicException('No transfer incoming account selected');
        }

        $secondTransaction->setAccountId($transferData['account_id']);

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($transferData['account_id']);
        kmwaAssert::instance($account, cashAccount::class);

        if ($transaction->getAccount()->getCurrency() !== $account->getCurrency()) {
            throw new kmwaNotImplementedException('No currency exchange logic');
        }

        cash()->getEntityPersister()->save($secondTransaction);

        return $secondTransaction;
    }
}
