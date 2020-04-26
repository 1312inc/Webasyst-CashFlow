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

    public function enableForecast()
    {
        $currencyModel = new shopCurrencyModel();

        $this->deleteFutureTransactions();
//        $amount = $this->settings->getManualForecast();
//        if (empty($amount)) {
//            $amount = $this->calculateAvgBill();
//        }
        $amounts = $this->calculateAvgBill();

        $forecastTransactionData = [];

        // включить надо для каждой витрины в отдельности или для одного
        if ($this->settings->getAccountId()) {
            /** @var cashAccount $account */
            $account = cash()->getEntityRepository(cashAccount::class)->findById($this->settings->getAccountId());
            kmwaAssert::instance($account, cashAccount::class);

            $forecastTransactionData[] = ['account' => $account->getId(), 'amount' => .0];

            foreach ($amounts as $i => $amount) {
                if ($amount['currency'] != $account->getCurrency()) {
                    $amounts[$i]['bill'] = $currencyModel->convert($amount, $amount['currency'], $account->getCurrency());
                }
            }

            $calculatedAmount[$account->getId()] = (float)array_sum(array_column($amounts, 'bill'));
        }
        foreach ($this->settings->getAccountsWithStorefront() as $storefront => $accountId) {

        }

        foreach ($forecastTransactionData as $forecastTransactionDatum) {
//            $this->getTransactionManager()->createForecastTransaction($forecastTransactionDatum['amount'], $forecastTransactionDatum['account'])

        }

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
        $sql = <<<SQL
select ifnull(sop.value, 'backend'),
       currency,
       sum(total) / count(total) bill
from shop_order
         left join shop_order_params sop on shop_order.id = sop.order_id and sop.name = 'storefront'
where paid_date > s:date
      %s
group by ifnull(sop.value, 'backend'), currency
SQL;

        if ($storefront) {
            $sql = sprintf($sql, $storefront === 'backend' ? 'and sop.value is null' : ' and sop.value = s:storefront');
        }

        $date = new DateTime("-{$lastNDays} days");

        return (new shopOrderModel())->query(
            $sql,
            ['date' => $date->format('Y-m-d'), 'storefront' => $storefront]
        )->fetchField('bill');
    }

    /**
     * @throws waException
     */
    private function deleteFutureTransactions()
    {
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $sql = "delete from {$model->getTableName()} where external_source = 'shop' and date > s:date";

        $dateToDelete = new DateTime();
        if ($this->settings->getTodayTransactions() === 0) {
            $dateToDelete->modify('yesterday');
        }

        $model->exec($sql, ['date' => $dateToDelete->format('Y-m-d')]);

        /** @var cashRepeatingTransactionModel $model */
        $model = cash()->getModel(cashRepeatingTransaction::class);
        $model->exec("delete from {$model->getTableName()} where external_source = 'shop'");
    }
}
