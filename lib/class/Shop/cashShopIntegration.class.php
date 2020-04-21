<?php

/**
 * Class cashShopIntegration
 */
class cashShopIntegration
{
    /**
     * @var cashShopSettings
     */
    private $settings;

    /**
     * cashShopIntegration constructor.
     */
    public function __construct()
    {
        $this->settings = new cashShopSettings();
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
        /** @var cashTransactionModel $model */
        $model = cash()->getModel(cashTransaction::class);
        $sql = <<<SQL
delete from {$model->getTableName()}
where external_source = 'shop' and
date > s:date
SQL;

        $dateToDelete = new DateTime();
        if ($this->settings->getTodayTransactions() === 0) {
            $dateToDelete->modify('yesterday');
        }

        $model->exec($sql, ['date' => $dateToDelete->format('Y-m-d')]);
    }

    public function turnedOn()
    {

    }

    /**
     * @return cashShopSettings
     */
    public function getSettings()
    {
        return $this->settings;
    }
}
