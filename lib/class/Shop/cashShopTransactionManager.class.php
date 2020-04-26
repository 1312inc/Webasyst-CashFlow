<?php

/**
 * Class cashShopTransactionManager
 */
class cashShopTransactionManager
{
    const INCOME  = 'income';
    const EXPENSE = 'expense';

    /**
     * @var cashShopSettings
     */
    private $settings;

    /**
     * cashShopOrderActionListener constructor.
     *
     * @param cashShopSettings $settings
     */
    public function __construct(cashShopSettings $settings)
    {
        $this->settings = $settings;
    }

    /**
     * @param int    $orderId
     * @param string $type
     * @param array  $params
     *
     * @return cashTransaction
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function createTransaction($orderId, $type, $params = [])
    {
        $order = new shopOrder($orderId);

        $amount = $this->getAmount($order, $type, $params);
        $externalHash = $this->generateExternalHash($order, $type, $amount);

        $transaction = cash()->getEntityRepository(cashTransaction::class)->findByFields(
            [
                'external_hash' => $externalHash,
                'external_source' => 'shop',
            ]
        );
        if ($transaction instanceof cashTransaction) {
            throw new kmwaRuntimeException(
                sprintf(
                    'Transaction based on order %s and type %s already exists: %d',
                    $order->getId(),
                    $type,
                    $transaction->getId()
                )
            );
        }

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        $transaction
            ->setDescription(
                sprintf_wp('Order %s by %s', shopHelper::encodeOrderId($order->getId()), $order->contact->getName())
            )
            ->setAccount($this->getAccount($order))
            ->setCategory($this->getCategory($type))
            ->setExternalHash($externalHash)
            ->setExternalSource('shop')
        ;

        // конвертнем валюту заказа в валюту аккаунта
        if ($order->currency !== $transaction->getAccount()->getCurrency()) {
            $amount = (new shopCurrencyModel())->convert(
                $amount,
                $order->currency,
                $transaction->getAccount()->getCurrency()
            );
        }
        $transaction->setAmount($amount);

        return $transaction;
    }

    /**
     * @param int|float    $amount
     * @param cashAccount  $account
     * @param cashCategory $category
     * @param string       $storefront
     *
     * @return cashRepeatingTransaction
     * @throws Exception
     */
    public function createForecastTransaction($amount, cashAccount $account, cashCategory $category, $storefront)
    {
        /** @var cashRepeatingTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashRepeatingTransaction::class)->createNew();

        $transaction
            ->setDescription('Продажи магазина (план)')
            ->setAccount($account)
            ->setCategory($category)
            ->setExternalHash($storefront)
            ->setAmount($amount)
            ->setExternalSource('shop');

        return $transaction;
    }

    /**
     * @param cashTransaction $transaction
     * @param array           $params
     *
     * @throws ReflectionException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function saveTransaction(cashTransaction $transaction, $params = [])
    {
        if (!cash()->getEntityPersister()->save($transaction)) {
            throw new kmwaRuntimeException(
                sprintf('Save new transaction error: %s', json_encode(cash()->getHydrator()->extract($transaction)))
            );
        }

        cash()->getLogger()->debug(
            sprintf(
                'Transaction %d created successfully! %s',
                $transaction->getId(),
                json_encode(cash()->getHydrator()->extract($transaction))
            )
        );

        // запишем в лог заказа
        if ($this->settings->isWriteToOrderLog()) {
            (new shopOrderLogModel())->add(
                array_merge(
                    $params,
                    [
                        'text' => sprintf_wp(
                            'A transaction %s %s created (%s)',
                            $transaction->getAmount(),
                            $transaction->getAccount()->getCurrency(),
                            $transaction->getAccount()->getName()
                        ),
                        'params' => ['cash_transaction_id' => $transaction->getId()],
                    ]
                )
            );

            cash()->getLogger()->debug('Transaction %d info added to order log!', $transaction->getId());
        }

        $this->settings
            ->incTodayTransactionsCount()
            ->saveStat();
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
                throw new kmwaRuntimeException(sprintf('Wrong transaction type %s', $type));
        }

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById($categoryId);
        kmwaAssert::instance($category, cashCategory::class);

        return $category;
    }

    /**
     * При рефанде можно вернуть только часть товаров, поэтому есть костылёк $params
     *
     * @param shopOrder $order
     * @param string    $type
     * @param array     $params
     *
     * @return float|int
     */
    private function getAmount(shopOrder $order, $type, $params = [])
    {
        if (isset($params['params']['refund_amount'])) {
            $amount = abs((float)$params['params']['refund_amount']);
        } else {
            $amount = abs($order->total);
        }

        $amount = $type === self::INCOME ? $amount : -$amount;

        return $amount;
    }

    /**
     * @param shopOrder $order
     * @param string    $type
     * @param int|float $amount
     *
     * @return string
     */
    private function generateExternalHash(shopOrder $order, $type, $amount)
    {
        return md5(sprintf('%s/%s/%s', $order->getId(), $type, $amount));
    }
}
