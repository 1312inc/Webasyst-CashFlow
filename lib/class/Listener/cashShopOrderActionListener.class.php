<?php

/**
 * Class cashShopOrderActionListener
 */
class cashShopOrderActionListener extends waEventHandler
{
    const INCOME  = 'income';
    const EXPENSE = 'expense';

    /**
     * @var cashTransactionFactory
     */
    private $factory;

    /**
     * @var cashShopSettings
     */
    private $settings;

    /**
     * cashShopOrderActionListener constructor.
     */
    public function __construct()
    {
        $this->factory = cash()->getEntityFactory(cashTransaction::class);
        $this->settings = new cashShopSettings();
    }

    /**
     * @param $params
     */
    public function execute(&$params)
    {
        if (!isset($params['action_id'])) {
            return;
        }

        try {
            if (in_array($params['action_id'], $this->settings->getIncomeActions())) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new income transaction for action %s', $params['action_id'])
                );

                $this->createIncomeTransaction($params['order_id']);

                return;
            }

            if (in_array($params['action_id'], $this->settings->getExpenseActions())) {
                cash()->getLogger()->debug(
                    sprintf('Okay, lets create new expense transaction for action %s', $params['action_id'])
                );

                $this->createExpenseTransaction($params['order_id'], $params);

                return;
            }
        } catch (Exception $ex) {
            cash()->getLogger()->error('Some error occurs on shop order transaction creation', $ex);
        }
    }

    /**
     * @param int $orderId
     *
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function createIncomeTransaction($orderId)
    {
        $order = new shopOrder($orderId);

        $transaction = $this->createTransaction($order, self::INCOME);
        $transaction->setAmount(abs($order->total));

        cash()->getEntityPersister()->save($transaction);

        cash()->getLogger()->debug(
            sprintf(
                'Income transaction %d created successfully! %s',
                $transaction->getId(),
                json_encode(cash()->getHydrator()->extract($transaction))
            )
        );
    }

    /**
     * @param int   $orderId
     * @param array $params
     *
     * @throws ReflectionException
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function createExpenseTransaction($orderId, $params = [])
    {
        $order = new shopOrder($orderId);

        $transaction = $this->createTransaction($order, self::INCOME);

        if (isset($params['params']['refund_amount'])) {
            $amount = -abs((float)$params['params']['refund_amount']);
        } else {
            $amount = -abs($order->total);
        }
        $transaction->setAmount($amount);

        cash()->getEntityPersister()->save($transaction);

        cash()->getLogger()->debug(
            sprintf(
                'Expense transaction %d created successfully! %s',
                $transaction->getId(),
                json_encode(cash()->getHydrator()->extract($transaction))
            )
        );
    }

    /**
     * @param shopOrder $order
     * @param string    $type
     *
     * @return cashTransaction
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function createTransaction(shopOrder $order, $type)
    {
        /** @var cashTransaction $transaction */
        $transaction = $this->factory->createNew();

        $transaction
            ->setDescription(
                sprintf_wp('Order %s by %s', shopHelper::encodeOrderId($order->getId()), $order->contact->getName())
            )
            ->setAccount($this->getAccount($order))
            ->setCategory($this->getCategory($type));

        return $transaction;
    }

    /**
     * @param shopOrder $order
     *
     * @return cashAccount
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function getAccount(shopOrder $order)
    {
        /** @var cashAccountRepository $rep */
        $rep = cash()->getEntityRepository(cashAccount::class);

        $accountId = $this->settings->getAccountId();
        if (empty($accountId)) {
            $storefront = isset($order['params']['storefront']) ? $order['params']['storefront'] : 'backend';
            $accountId = $this->settings->getAccountByStorefront($storefront);
            if (empty($accountId)) {
                throw new kmwaRuntimeException(sprintf('No account for storefront %s', $storefront));
            }
        }

        /** @var cashAccount $account */
        $account = $rep->findById($accountId);
        kmwaAssert::instance($account, cashAccount::class);

        if ($account->getIsArchived()) {
            throw new kmwaRuntimeException(
                sprintf('Account %s (%s) is archived', $account->getName(), $account->getId())
            );
        }

        return $account;
    }

    /**
     * @param string $type
     *
     * @return cashCategory
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function getCategory($type)
    {
        switch ($type) {
            case self::INCOME:
                $categoryId = $this->settings->getCategoryIncomeId();
                break;

            case self::EXPENSE:
                $categoryId = $this->settings->getCategoryExpenseId();
                break;

            default:
                throw new kmwaRuntimeException(sprintf('Wront transaction type %s', $type));
        }

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById($categoryId);
        kmwaAssert::instance($category, cashCategory::class);

        return $category;
    }
}
