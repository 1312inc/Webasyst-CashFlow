<?php

/**
 * Class cashShopIntegration
 */
class cashShopIntegration
{
    const DAYS_FOR_AVG_BILL_CALCULATION = 30;
    const SESSION_SSIMPORT = 'cash.shopscript_import';

    /**
     * @var cashShopSettings
     */
    private $settings;

    /**
     * @var cashShopTransactionFactory
     */
    private $transactionFactory;

    /**
     * @var shopCurrencyModel
     */
    private $shopCurrencyModel;

    /**
     * cashShopIntegration constructor.
     */
    public function __construct()
    {
        $this->settings = new cashShopSettings();
        $this->shopExists();
    }

    /**
     * @return cashShopTransactionFactory
     */
    public function getTransactionFactory(): cashShopTransactionFactory
    {
        if ($this->transactionFactory === null) {
            $this->transactionFactory = new cashShopTransactionFactory($this);
        }

        return $this->transactionFactory;
    }

    /**
     * @return bool
     * @throws waException
     */
    public function shopExists(): bool
    {
        if (!wa()->appExists('shop')) {
            return false;
        }

        if ($this->shopIsOld()) {
            return false;
        }

        wa('shop');

        return true;
    }

    /**
     * @return bool
     * @throws waException
     */
    public function shopIsOld(): bool
    {
        return (bool)version_compare(wa()->getVersion('shop'), '8.0.0.0', '<');
    }

    /**
     * @throws waException
     */
    public function turnedOff(): void
    {
        $this->deleteFutureForecastTransactions();
        $this->settings
            ->resetStat()
            ->saveStat();
    }

    /**
     * @throws kmwaAssertException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function turnedOn(): void
    {
        if ($this->settings->isEnableForecast()) {
            $this->enableForecast();
        }
    }

    /**
     * @throws waException
     */
    public function disableForecast(): void
    {
        $this->deleteFutureForecastTransactions();
        $this->settings
            ->resetStat()
            ->saveStat();
    }

    /**
     * @return bool
     * @throws kmwaAssertException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function enableForecast(): bool
    {
        $this->deleteFutureForecastTransactions();

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->settings->getAccountId());
        if (!$account) {
            return false;
        }

        $amount = $this->settings->isAutoForecast()
            ? $this->getShopAvgAmount($account->getCurrency())
            : $this->settings->getManualForecast();

        $category = cash()->getEntityRepository(cashCategory::class)->findById($this->settings->getCategoryIncomeId());
        if (!$category) {
            return false;
        }

        if ($amount) {
            $transaction = $this->getTransactionFactory()->createForecastTransaction($amount, $account, $category);

            $saver = new cashRepeatingTransactionSaver();
            $repeatingSettings = new cashRepeatingTransactionSettingsDto();
            $repeatingSettings->end_type = cashRepeatingTransaction::REPEATING_END_NEVER;
            $repeatingSettings->interval = cashRepeatingTransaction::INTERVAL_DAY;
            $repeatingSettings->frequency = cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY;
            $repeatingTransaction = $saver->saveFromTransaction($transaction, $repeatingSettings, true);

            (new cashTransactionRepeater())->repeat($repeatingTransaction->newTransaction, new DateTime('tomorrow'));
        }

        return true;
    }

    /**
     * Поменяет тип прогноза, создаст нужную повторяющуюся транзакцию и повторит ее
     *
     * @throws kmwaAssertException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function changeForecastType(): void
    {
        $transaction = $this->getForecastRepeatingTransaction();
        if (!$transaction instanceof cashRepeatingTransaction) {
            $this->enableForecast();
        } else {
            $amount = $this->settings->isAutoForecast()
                ? $this->getShopAvgAmount($this->settings->getAccount()->getCurrency())
                : $this->settings->getManualForecast();
            if ($amount) {
                $transaction
                    ->setAmount($amount)
                    ->setAccount(
                        cash()->getEntityRepository(cashAccount::class)->findById($this->getSettings()->getAccountId())
                    )
                    ->setCategory($this->getSettings()->getCategoryIncome());

                cash()->getEntityPersister()->update($transaction);

                $this->updateShopTransactionsAfterDate(new DateTime(), $transaction);
            }
        }
    }

    /**
     * @return cashShopSettings
     */
    public function getSettings(): cashShopSettings
    {
        return $this->settings;
    }

    /**
     * @param int    $lastNDays
     * @param string $storefront
     *
     * @return float
     */
    public function calculateAvgBill($lastNDays = self::DAYS_FOR_AVG_BILL_CALCULATION, $storefront = ''): float
    {
//        $sql = <<<SQL
//select ifnull(sop.value, 'backend'),
//       currency,
//       sum(total) / count(total) bill
//from shop_order
//         left join shop_order_params sop on shop_order.id = sop.order_id and sop.name = 'storefront'
//where paid_date > s:date
//      %s
//group by ifnull(sop.value, 'backend'), currency
//SQL;
        $sql = <<<SQL
select sum(total) / {$lastNDays} bill
from shop_order
where paid_date > s:date
SQL;

        $date = new DateTime("-{$lastNDays} days");

        return round((float)(new shopOrderModel())->query(
            $sql,
            ['date' => $date->format('Y-m-d'), 'storefront' => $storefront]
        )->fetchField());
    }


    /**
     * @param string $currency
     * @param int $days
     *
     * @return float
     * @throws waException
     */
    public function getShopAvgAmount($currency, $days = self::DAYS_FOR_AVG_BILL_CALCULATION): float
    {
        $amount = $this->calculateAvgBill($days);
        $defaultCurrency = wa('shop')->getConfig()->getCurrency();
        $amount = $this->convert($amount, $defaultCurrency, $currency);

        return $amount;
    }

    /**
     * @throws kmwaAssertException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function actualizeForecastTransaction(): void
    {
        if (!$this->settings->isEnabled()) {
            return;
        }

        if (!$this->settings->isEnableForecast()) {
            return;
        }

        if (!$this->settings->isAutoForecast()) {
            return;
        }

        $this->changeForecastType();
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function saveTransactions(cashShopCreateTransactionDto $dto): void
    {
        cash()->getModel()->startTransaction();
        try {
            $transactionListMessage = [];

            if (!cash()->getEntityPersister()->save($dto->mainTransaction)) {
                throw new kmwaRuntimeException(
                    sprintf(
                        'Save new transaction error: %s',
                        json_encode(cash()->getHydrator()->extract($dto->mainTransaction))
                    )
                );
            }

            cash()->getLogger()->debug(
                sprintf(
                    'Transaction %d created successfully! %s',
                    $dto->mainTransaction->getId(),
                    json_encode(cash()->getHydrator()->extract($dto->mainTransaction))
                )
            );
            $transactionListMessage[] = sprintf(
                '%s%s %s @ %s (pl2e)',
                $dto->mainTransaction->getAmount() < 0 ? '' : '+',
                $dto->mainTransaction->getAmount(),
                $dto->mainTransaction->getAccount()->getCurrency(),
                $dto->mainTransaction->getCategory() ? $dto->mainTransaction->getCategory()->getName() : ''
            );

            if ($dto->purchaseTransaction) {
                if (!cash()->getEntityPersister()->save($dto->purchaseTransaction)) {
                    throw new kmwaRuntimeException(
                        sprintf(
                            'Save new purchase transaction error: %s',
                            json_encode(cash()->getHydrator()->extract($dto->purchaseTransaction))
                        )
                    );
                }

                $transactionListMessage[] = sprintf(
                    '%s%s %s @ %s (pl2e)',
                    $dto->purchaseTransaction->getAmount() < 0 ? '' : '+',
                    $dto->purchaseTransaction->getAmount(),
                    $dto->purchaseTransaction->getAccount()->getCurrency(),
                    $dto->purchaseTransaction->getCategory() ? $dto->purchaseTransaction->getCategory()->getName() : ''
                );
            }

            if ($dto->shippingTransaction) {
                if (!cash()->getEntityPersister()->save($dto->shippingTransaction)) {
                    throw new kmwaRuntimeException(
                        sprintf(
                            'Save new shipping transaction error: %s',
                            json_encode(cash()->getHydrator()->extract($dto->shippingTransaction))
                        )
                    );
                }

                $transactionListMessage[] = sprintf(
                    '%s%s %s @ %s (pl2e)',
                    $dto->shippingTransaction->getAmount() < 0 ? '' : '+',
                    $dto->shippingTransaction->getAmount(),
                    $dto->shippingTransaction->getAccount()->getCurrency(),
                    $dto->shippingTransaction->getCategory() ? $dto->shippingTransaction->getCategory()->getName() : ''
                );
            }

            if ($dto->taxTransaction) {
                if (!cash()->getEntityPersister()->save($dto->taxTransaction)) {
                    throw new kmwaRuntimeException(
                        sprintf(
                            'Save new tax transaction error: %s',
                            json_encode(cash()->getHydrator()->extract($dto->taxTransaction))
                        )
                    );
                }

                $transactionListMessage[] = sprintf(
                    '%s%s %s @ %s (pl2e)',
                    $dto->taxTransaction->getAmount() < 0 ? '' : '+',
                    $dto->taxTransaction->getAmount(),
                    $dto->taxTransaction->getAccount()->getCurrency(),
                    $dto->taxTransaction->getCategory() ? $dto->taxTransaction->getCategory()->getName() : ''
                );
            }

            // запишем в лог заказа
            if ($this->settings->isWriteToOrderLog() && !empty($dto->params['order_id'])) {
                $message = sprintf(
                    _w('%d transactions created in the Cash Flow app:').'%s%s',
                    count($transactionListMessage),
                    '<br>',
                    implode('<br>', $transactionListMessage)
                );
                (new shopOrderLogModel())->add(
                    array_merge(
                        $dto->params,
                        [
                            'text' => $message,
                            'params' => ['cash_transaction_id' => $dto->mainTransaction->getId()],
                        ]
                    )
                );

                cash()->getLogger()->debug('Transaction info added to order log: %s!', $message);
            }

            $this->settings
                ->incTodayTransactionsCount()
                ->saveStat();

            cash()->getModel()->commit();
        } catch (Exception $ex) {
            cash()->getModel()->rollback();

            throw new kmwaRuntimeException(
                sprintf('Save new transactions error: %s', $ex->getMessage()), $ex->getCode(), $ex
            );
        }
    }

    /**
     * @param DateTime $dateTime
     *
     * @return cashTransaction|null
     * @throws waException
     */
    public function getForecastTransactionForDate(DateTime $dateTime): ?cashTransaction
    {
        return cash()->getEntityRepository(cashTransaction::class)->findByFields(
            [
                'external_source' => 'shop',
                'external_hash' => cashShopTransactionFactory::HASH_FORECAST,
                'date' => $dateTime->format('Y-m-d'),
            ]
        ) ?: null;
    }

    /**
     * @return cashRepeatingTransaction|null
     * @throws waException
     */
    public function getForecastRepeatingTransaction(): ?cashRepeatingTransaction
    {
        return cash()->getEntityRepository(cashRepeatingTransaction::class)->findByFields(
            [
                'external_source' => 'shop',
                'external_hash' => cashShopTransactionFactory::HASH_FORECAST,
            ]
        ) ?: null;
    }

    /**
     * @param DateTime $dateTime
     * @param bool     $include
     *
     * @return null
     * @throws waException
     */
    public function deleteForecastTransactionBeforeDate(DateTime $dateTime, $include = false)
    {
        return cash()->getModel(cashTransaction::class)->exec(
            sprintf(
                'delete from cash_transaction where date %s s:date and external_source = s:external_source and external_hash = s:external_hash',
                $include ? '<=' : '<'
            ),
            [
                'external_source' => 'shop',
                'external_hash' => cashShopTransactionFactory::HASH_FORECAST,
                'date' => $dateTime->format('Y-m-d'),
            ]
        );
    }

    /**
     * @param cashEventOnCount $event
     *
     * @throws waDbException
     * @throws waException
     * @throws kmwaAssertException
     */
    public function onCount(cashEventOnCount $event)
    {
        $this->actualizeForecastTransaction();
    }

    /**
     * @param float  $amount
     * @param string $from
     * @param string $to
     *
     * @return mixed
     */
    public function convert($amount, $from, $to)
    {
        if ($from !== $to
            && $this->getShopCurrencyModel()->getById($to)
            && $this->getShopCurrencyModel()->getById($from)
        ) {
            $amount = $this->getShopCurrencyModel()->convert($amount, $from, $to);
        }

        return $amount;
    }

    /**
     * @param cashRepeatingTransaction $transaction
     *
     * @return bool
     */
    public function isShopForecastRepeatingTransaction(cashRepeatingTransaction $transaction): bool
    {
        return $transaction->getExternalSource() === 'shop'
            && $transaction->getExternalHash() === cashShopTransactionFactory::HASH_FORECAST;
    }

    /**
     * @param DateTime|null $after
     *
     * @return int
     */
    public function countOrdersToProcess(DateTime $after = null): int
    {
        if ($this->shopExists()) {
            return (int)(new shopOrderModel())
                ->select('count(*)')
                ->where(
                    sprintf('paid_date is not null %s', $after ? 'and create_datetime >= s:after' : ''),
                    ['after' => $after ? $after->format('Y-m-d H:i:s') : '']
                )
                ->fetchField();
        }

        return 0;
    }

    /**
     * @throws waException
     */
    public function deleteAllShopTransactions()
    {
        cash()->getModel(cashTransaction::class)->deleteBySource('shop');
    }

    /**
     * @return array
     */
    public function getOrderBounds(): array
    {
        $dates = (new shopOrderModel())
            ->query(
                'select min(create_datetime), max(create_datetime) from shop_order where paid_date is not null'
            )
            ->fetchArray();

        return ['min' => $dates[0] ?? date('Y-m-d H:i:s'), 'max' => $dates[1] ?? date('Y-m-d H:i:s')];
    }

    /**
     * @throws waException
     */
    private function deleteFutureForecastTransactions()
    {
        $dateToDelete = new DateTime();
        if ($this->settings->getTodayTransactions() === 0) {
            $dateToDelete->modify('yesterday');
        }

        cash()->getModel(cashTransaction::class)->deleteBySourceAndHashAfterDate(
            'shop',
            cashShopTransactionFactory::HASH_FORECAST,
            $dateToDelete->format('Y-m-d')
        );

        cash()->getModel(cashRepeatingTransaction::class)->deleteAllBySourceAndHash(
            'shop',
            cashShopTransactionFactory::HASH_FORECAST
        );
    }

    /**
     * @param DateTime        $date
     * @param cashTransaction $transaction
     *
     * @return bool|resource
     * @throws waException
     */
    private function updateShopTransactionsAfterDate(DateTime $date, cashTransaction $transaction)
    {
        return cash()->getModel(cashTransaction::class)->updateAmountBySourceAndHashAfterDate(
            'shop',
            cashShopTransactionFactory::HASH_FORECAST,
            $date->format('Y-m-d'),
            [
                ['f', 'amount', $transaction->getAmount()],
                ['i', 'account_id', $transaction->getAccountId()],
                ['i', 'category_id', $transaction->getCategoryId()],
            ]
        );
    }

    /**
     * @return shopCurrencyModel
     */
    private function getShopCurrencyModel(): shopCurrencyModel
    {
        if ($this->shopCurrencyModel === null) {
            $this->shopCurrencyModel = new shopCurrencyModel();
        }

        return $this->shopCurrencyModel;
    }
}
