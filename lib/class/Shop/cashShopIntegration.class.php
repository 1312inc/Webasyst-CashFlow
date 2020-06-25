<?php

/**
 * Class cashShopIntegration
 */
class cashShopIntegration
{
    const DAYS_FOR_AVG_BILL_CALCULATION = 30;

    /**
     * @var cashShopSettings
     */
    private $settings;

    /**
     * @var cashShopTransactionFactory
     */
    private $transactionFactory;

    /**
     * cashShopIntegration constructor.
     */
    public function __construct()
    {
        $this->settings = new cashShopSettings();
    }

    /**
     * @return cashShopTransactionFactory
     */
    public function getTransactionFactory()
    {
        if ($this->transactionFactory === null) {
            $this->transactionFactory = new cashShopTransactionFactory($this->settings);
        }

        return $this->transactionFactory;
    }

    /**
     * @return bool
     * @throws waException
     */
    public function shopExists()
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
    public function shopIsOld()
    {
        return (bool)version_compare(wa()->getVersion('shop'), '8.0.0.0', '<');
    }

    /**
     * @throws waException
     */
    public function turnedOff()
    {
        $this->deleteFutureTransactions();
        $this->settings
            ->resetStat()
            ->saveStat();
    }

    /**
     * @throws kmwaAssertException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function turnedOn()
    {
        if ($this->settings->isEnableForecast()) {
            $this->enableForecast();
        }
    }

    /**
     * @throws waException
     */
    public function disableForecast()
    {
        $this->deleteFutureTransactions();
        $this->settings
            ->resetStat()
            ->saveStat();
    }

    /**
     * @throws kmwaAssertException
     * @throws waException
     * @throws kmwaRuntimeException
     */
    public function enableForecast()
    {
        $this->deleteFutureTransactions();

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->settings->getAccountId());
        kmwaAssert::instance($account, cashAccount::class);

        $amount = $this->settings->isAutoForecast() ? $this->getShopAvgAmount() : $this->settings->getManualForecast();

        $category = cash()->getEntityRepository(cashCategory::class)->findById($this->settings->getCategoryIncomeId());
        kmwaAssert::instance($category, cashCategory::class);

        $transaction = $this->getTransactionFactory()->createForecastTransaction($amount, $account, $category);

        $saver = new cashRepeatingTransactionSaver();
        $repeatingSettings = new cashRepeatingTransactionSettingsDto();
        $repeatingSettings->end_type = cashRepeatingTransaction::REPEATING_END_NEVER;
        $repeatingSettings->interval = cashRepeatingTransaction::INTERVAL_DAY;
        $repeatingSettings->frequency = cashRepeatingTransaction::DEFAULT_REPEATING_FREQUENCY;
        $repeatingTransaction = $saver->saveFromTransaction($transaction, $repeatingSettings, true);

        (new cashTransactionRepeater())->repeat($repeatingTransaction , new DateTime());
    }

    /**
     * @throws kmwaAssertException
     * @throws waException
     */
    public function changeForecastType()
    {
        $amount = $this->settings->isAutoForecast() ? $this->getShopAvgAmount() : $this->settings->getManualForecast();

        $transaction = $this->getForecastRepeatingTransaction();
        if (!$transaction instanceof cashRepeatingTransaction) {
            return;
        }

        $transaction->setAmount($amount);
        cash()->getEntityPersister()->update($transaction);

        $this->updateShopTransactionsAfterDate(new DateTime(), $amount);
    }

    /**
     * @return cashShopSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param int    $lastNDays
     * @param string $storefront
     *
     * @return float
     */
    public function calculateAvgBill($lastNDays = self::DAYS_FOR_AVG_BILL_CALCULATION, $storefront = '')
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
select sum(total) / count(total) bill
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
     * @throws waDbException
     * @throws waException
     * @throws kmwaAssertException
     */
    public function actualizeForecastTransaction()
    {
        if (!$this->settings->isEnableForecast()) {
            return;
        }

        if (!$this->settings->isAutoForecast()) {
            return;
        }

        if ($this->settings->isForecastActualizedToday()) {
            return;
        }

        $transaction = $this->getForecastRepeatingTransaction();
        if (!$transaction instanceof cashRepeatingTransaction) {
            return;
        }

        $today = new DateTime();
        $amount = $this->getShopAvgAmount();

        if ($transaction->getAmount() == $amount) {
            $this->settings
                ->setForecastActualizedToday(true)
                ->saveStat();

            return;
        }

        $this->updateShopTransactionsAfterDate($today, $amount);

        $transaction->setAmount($amount);
        if (cash()->getEntityPersister()->update($transaction)) {
            $this->settings
                ->setForecastActualizedToday(true)
                ->saveStat();
        }
    }

    /**
     * @param cashShopCreateTransactionDto $dto
     *
     * @throws ReflectionException
     * @throws kmwaRuntimeException
     * @throws waException
     */
    public function saveTransactions(cashShopCreateTransactionDto $dto)
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
                $dto->mainTransaction->getAccount()->getName()
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
                    $dto->mainTransaction->getAmount() < 0 ? '' : '+',
                    $dto->purchaseTransaction->getAmount(),
                    $dto->purchaseTransaction->getAccount()->getCurrency(),
                    $dto->purchaseTransaction->getAccount()->getName()
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
                    $dto->mainTransaction->getAmount() < 0 ? '' : '+',
                    $dto->shippingTransaction->getAmount(),
                    $dto->shippingTransaction->getAccount()->getCurrency(),
                    $dto->shippingTransaction->getAccount()->getName()
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
                    $dto->mainTransaction->getAmount() < 0 ? '' : '+',
                    $dto->taxTransaction->getAmount(),
                    $dto->taxTransaction->getAccount()->getCurrency(),
                    $dto->taxTransaction->getAccount()->getName()
                );
            }

            // запишем в лог заказа
            if ($this->settings->isWriteToOrderLog() && !empty($dto->params['order_id'])) {
                $message = sprintf(
                    "%d transactions created in the Cash app:%s%s",
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
    public function getForecastTransactionForDate(DateTime $dateTime)
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
    public function getForecastRepeatingTransaction()
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
     *
     * @return null
     * @throws waException
     */
    public function deleteForecastTransactionForDate(DateTime $dateTime)
    {
        return cash()->getModel(cashTransaction::class)->deleteByField(
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
     * @throws waException
     */
    private function deleteFutureTransactions()
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
     * @param DateTime $date
     * @param float    $amount
     *
     * @return bool|resource
     * @throws waException
     */
    private function updateShopTransactionsAfterDate(DateTime $date, $amount)
    {
        return cash()->getModel(cashTransaction::class)->updateAmountBySourceAndHashAfterDate(
            'shop',
            cashShopTransactionFactory::HASH_FORECAST,
            $date->format('Y-m-d'),
            $amount
        );
    }

    /**
     * @return float
     * @throws kmwaAssertException
     * @throws waDbException
     * @throws waException
     */
    private function getShopAvgAmount()
    {
        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->settings->getAccountId());
        kmwaAssert::instance($account, cashAccount::class);

        $amount = $this->calculateAvgBill(self::DAYS_FOR_AVG_BILL_CALCULATION);
        $defaultCurrency = wa('shop')->getConfig()->getCurrency();
        if ($defaultCurrency != $account->getCurrency()) {
            $currencyModel = new shopCurrencyModel();
            $amount = $currencyModel->convert($amount, $defaultCurrency, $account->getCurrency());
        }

        return $amount;
    }
}
