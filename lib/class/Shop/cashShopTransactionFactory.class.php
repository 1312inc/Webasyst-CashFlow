<?php

/**
 * Class cashShopTransactionFactory
 */
class cashShopTransactionFactory
{
    const
        INCOME = 'income',
        EXPENSE = 'expense',
        HASH_FORECAST = 'forecast';

    /**
     * @var cashShopIntegration
     */
    private $shopIntegration;

    /**
     * cashShopOrderActionListener constructor.
     *
     * @param cashShopIntegration $shopIntegration
     */
    public function __construct(cashShopIntegration $shopIntegration)
    {
        $this->shopIntegration = $shopIntegration;
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @return bool
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function createIncomeTransaction(cashShopCreateTransactionDto $dto): bool
    {
        $dto->mainTransaction = $this->createTransaction($dto, self::INCOME);
        $dto->mainTransaction->setDescription(
            sprintf_wp('Order %s by %s', $dto->encodedOrderId, $dto->order->contact->getName())
        );

        if ($this->shopIntegration->getSettings()->getCategoryPurchaseId()) {
            $this->createPurchaseTransaction($dto);
        }

        if ($this->shopIntegration->getSettings()->getCategoryShippingId()) {
            $this->createShippingTransaction($dto);
        }

        if ($this->shopIntegration->getSettings()->getCategoryTaxId()) {
            $this->createTaxTransaction($dto);
        }

        return true;
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @return bool
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function createExpenseTransaction(cashShopCreateTransactionDto $dto): bool
    {
        $dto->mainTransaction = $this->createTransaction($dto, self::EXPENSE);
        $dto->mainTransaction->setDescription(
            sprintf_wp('Refund for order %s by %s', $dto->encodedOrderId, $dto->order->contact->getName())
        );

        return true;
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @return bool
     * @throws kmwaAssertException
     * @throws waException
     */
    public function createPurchaseTransaction(cashShopCreateTransactionDto $dto): bool
    {
        $amount = 0;
        foreach ($dto->order->items as $item) {
            $amount += ($item['purchase_price'] * $item['quantity']);
        }

        if ($amount <= 0) {
            return false;
        }

        $externalHash = $this->generateExternalHash($dto->order, 'purchase', $amount);

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById(
            $this->shopIntegration->getSettings()->getCategoryPurchaseId()
        );
        kmwaAssert::instance($category, cashCategory::class);

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        $transaction
            ->setDescription(sprintf_wp('Purchase costs for order %s', $dto->encodedOrderId))
            ->setAccount($dto->mainTransaction->getAccount())
            ->setCategory($category)
            ->setExternalHash($externalHash)
            ->setDate($dto->mainTransaction->getDate())
            ->setExternalSource('shop')
            ->setExternalId((int) $dto->order->getId())
            ->setExternalData(
                [
                    'id' => $dto->order->getId(),
                    'type' => 'purchase',
                    'linked_transaction' => $dto->mainTransaction->getId(),
                ]
            );

        // конвертнем валюту заказа в валюту аккаунта
        $amount = (new cashShopIntegration())->convert(
            $amount,
            $dto->order->currency,
            $transaction->getAccount()->getCurrency()
        );

        $transaction->setAmount(-$amount);

        $dto->purchaseTransaction = $transaction;

        return true;
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @return bool
     * @throws kmwaAssertException
     * @throws waException
     */
    public function createShippingTransaction(cashShopCreateTransactionDto $dto): bool
    {
        $type = 'shipping';
        $amount = $dto->order->shipping;
        if ($amount <= 0) {
            return false;
        }

        $externalHash = $this->generateExternalHash($dto->order, $type, $amount);

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById(
            $this->shopIntegration->getSettings()->getCategoryShippingId()
        );
        kmwaAssert::instance($category, cashCategory::class);

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        $transaction
            ->setDescription(sprintf_wp('Shipping costs for order %s', $dto->encodedOrderId))
            ->setAccount($dto->mainTransaction->getAccount())
            ->setCategory($category)
            ->setExternalHash($externalHash)
            ->setDate($dto->mainTransaction->getDate())
            ->setExternalSource('shop')
            ->setExternalData(
                [
                    'id' => $dto->order->getId(),
                    'type' => $type,
                    'linked_transaction' => $dto->mainTransaction->getId(),
                ]
            );

        // конвертнем валюту заказа в валюту аккаунта
        $amount = (new cashShopIntegration())->convert(
            $amount,
            $dto->order->currency,
            $transaction->getAccount()->getCurrency()
        );

        $transaction->setAmount(-$amount);

        $dto->shippingTransaction = $transaction;

        return true;
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @return bool
     * @throws kmwaAssertException
     * @throws waException
     */
    public function createTaxTransaction(cashShopCreateTransactionDto $dto): bool
    {
        $type = 'tax';
        $amount = $dto->order->tax;
        if ($amount <= 0) {
            return false;
        }

        $externalHash = $this->generateExternalHash($dto->order, $type, $amount);

        /** @var cashCategory $category */
        $category = cash()->getEntityRepository(cashCategory::class)->findById(
            $this->shopIntegration->getSettings()->getCategoryTaxId()
        );
        kmwaAssert::instance($category, cashCategory::class);

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        $transaction
            ->setDescription(sprintf_wp('Taxes for order %s', $dto->encodedOrderId))
            ->setAccount($dto->mainTransaction->getAccount())
            ->setCategory($category)
            ->setExternalHash($externalHash)
            ->setDate($dto->mainTransaction->getDate())
            ->setExternalSource('shop')
            ->setExternalData(
                [
                    'id' => $dto->order->getId(),
                    'type' => $type,
                    'linked_transaction' => $dto->mainTransaction->getId(),
                ]
            );

        // конвертнем валюту заказа в валюту аккаунта
        $amount = (new cashShopIntegration())->convert(
            $amount,
            $dto->order->currency,
            $transaction->getAccount()->getCurrency()
        );

        $transaction->setAmount(-$amount);

        $dto->shippingTransaction = $transaction;

        return true;
    }

    /**
     * @param int|float    $amount
     * @param cashAccount  $account
     * @param cashCategory $category
     *
     * @return cashTransaction
     * @throws Exception
     */
    public function createForecastTransaction($amount, cashAccount $account, cashCategory $category): cashTransaction
    {
        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();
        $date = new DateTime('tomorrow');

        $transaction
            ->setDescription(_wd(cashConfig::APP_ID, 'Planned online store sales'))
            ->setAccount($account)
            ->setCategory($category)
            ->setExternalHash(self::HASH_FORECAST)
            ->setAmount($amount)
            ->setDate($date->format('Y-m-d'))
            ->setDatetime($date->format('Y-m-d H:i:s'))
            ->setExternalSource('shop');

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
    private function getAccount(shopOrder $order): cashAccount
    {
        /** @var cashAccountRepository $rep */
        $rep = cash()->getEntityRepository(cashAccount::class);

        $accountId = $this->shopIntegration->getSettings()->getAccountId();
        if (empty($accountId)) {
            $storefront = isset($order['params']['storefront']) ? $order['params']['storefront'] : 'backend';
            $accountId = $this->shopIntegration->getSettings()->getAccountByStorefront($storefront);
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
    private function getCategory($type): cashCategory
    {
        switch ($type) {
            case self::INCOME:
                $categoryId = $this->shopIntegration->getSettings()->getCategoryIncomeId();
                break;

            case self::EXPENSE:
                $categoryId = $this->shopIntegration->getSettings()->getCategoryExpenseId();
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
    private function generateExternalHash(shopOrder $order, $type, $amount): string
    {
        return md5(sprintf('%s/%s/%s', $order->getId(), $type, $amount));
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     * @param string                       $type
     *
     * @return cashTransaction
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    private function createTransaction(cashShopCreateTransactionDto $dto, $type): cashTransaction
    {
        $amount = $this->getAmount($dto->order, $type, $dto->params);
        $externalHash = $this->generateExternalHash($dto->order, $type, $amount);

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
                    $dto->order->getId(),
                    $type,
                    $transaction->getId()
                )
            );
        }

        /** @var cashTransaction $transaction */
        $transaction = cash()->getEntityFactory(cashTransaction::class)->createNew();

        $transaction
            ->setAccount($this->getAccount($dto->order))
            ->setCategory($this->getCategory($type))
            ->setExternalHash($externalHash)
            ->setDate($dto->order->paid_date)
            ->setExternalSource('shop')
            ->setExternalData(['id' => $dto->order->getId()])
            ->setContractorContactId($dto->order->contact->getId());

        // конвертнем валюту заказа в валюту аккаунта
        $amount = (new cashShopIntegration())->convert(
            $amount,
            $dto->order->currency,
            $transaction->getAccount()->getCurrency()
        );

        $transaction->setAmount($amount);

        return $transaction;
    }
}
