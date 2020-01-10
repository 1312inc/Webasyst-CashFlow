<?php

/**
 * Class cashTransactionSaver
 */
class cashTransactionSaver
{
    /**
     * @var string
     */
    private $error = '';

    /**
     * @param array $data
     *
     * @return bool|cashTransaction
     */
    public function save(array $data)
    {
        if (!$this->validate($data)) {
            return false;
        }

        try {
            /** @var cashTransaction $transaction */
            if ($data['id']) {
                $transaction = cash()->getEntityRepository(cashTransaction::class)->findById($data['id']);
                kmwaAssert::instance($transaction, cashTransaction::class);
                unset($data['id']);
            } else {
                $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
            }

            if (!empty($data['category_id'])) {
                /** @var cashCategory $category */
                $category = cash()->getEntityRepository(cashCategory::class)->findById($data['category_id']);
                kmwaAssert::instance($category, cashCategory::class);
                if ($category->isExpense() && $data['amount'] > 0) {
                    $data['amount'] = -$data['amount'];
                }
            }

            cash()->getHydrator()->hydrate($transaction, $data);
            cash()->getEntityPersister()->save($transaction);

            return $transaction;
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
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }
}
