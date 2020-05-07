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
     * @var cashShopTransactionManager
     */
    private $transactionManager;

    /**
     * cashShopIntegration constructor.
     */
    public function __construct()
    {
        $this->settings = new cashShopSettings();
    }

    /**
     * @return cashShopTransactionManager
     */
    public function getTransactionManager()
    {
        if ($this->transactionManager === null) {
            $this->transactionManager = new cashShopTransactionManager($this->settings);
        }

        return $this->transactionManager;
    }

    /**
     * @return bool
     */
    public function shopExists()
    {
        if (!wa()->appExists('shop')) {
            return false;
        }

        wa('shop');

        return true;
    }

    /**
     * @throws waException
     */
    public function turnedOff()
    {
        $this->deleteFutureTransactions();
    }

    public function turnedOn()
    {
        if ($this->settings->isEnableForecast()) {
            $this->enableForecast();
        }
    }

    public function disableForecast()
    {
        $this->deleteFutureTransactions();
    }

    /**
     * @throws kmwaAssertException
     * @throws waException
     */
    public function enableForecast()
    {
        $this->deleteFutureTransactions();

        /** @var cashAccount $account */
        $account = cash()->getEntityRepository(cashAccount::class)->findById($this->settings->getAccountId());
        kmwaAssert::instance($account, cashAccount::class);

        $amount = 0;
        if ($this->settings->isAutoForecast()) {
            $amounts = $this->calculateAvgBill(450);
            $currencyModel = new shopCurrencyModel();
            foreach ($amounts as $currency => $bill) {
                if ($currency != $account->getCurrency()) {
                    $bill = $currencyModel->convert($bill, $currency, $account->getCurrency());
                }
                $amount += $bill;
            }
        } else {
            $amount = $this->settings->getManualForecast();
        }

        $category = cash()->getEntityRepository(cashCategory::class)->findById($this->settings->getCategoryIncomeId());
        kmwaAssert::instance($category, cashCategory::class);

        $transaction = $this->getTransactionManager()->createForecastTransaction($amount, $account, $category);

        $saver = new cashRepeatingTransactionSaver();
        $repeatingSettings = new cashRepeatingTransactionSettingsDto();
        $saver->saveFromTransaction($transaction, $repeatingSettings, true);
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
     * @return array
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
select currency,
       sum(total) / count(total) bill
from shop_order
where paid_date > s:date
group by currency
SQL;

        $date = new DateTime("-{$lastNDays} days");

        return (new shopOrderModel())->query(
            $sql,
            ['date' => $date->format('Y-m-d'), 'storefront' => $storefront]
        )->fetchAll('currency', 1);
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

        cash()->getModel(cashTransaction::class)->deleteBySourceAfterDate('shop', $dateToDelete->format('Y-m-d'));

        cash()->getModel(cashRepeatingTransaction::class)
            ->deleteAllBySourceAndHash('shop', cashShopTransactionManager::HASH_FORECAST);
    }
}
