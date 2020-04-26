<?php

/**
 * Class cashShopSettings
 */
class cashShopSettings implements JsonSerializable
{
    use cashDtoJsonSerializableTrait;

    const AUTO_DAILY_SALES = 1;
    const MANUAL_DAILY_SALES = 2;

    /**
     * @var bool
     */
    private $enabled = 0;

    /**
     * @var int
     */
    private $accountId = 0;

    /**
     * @var array
     */
    private $accountByStorefronts = [];

    /**
     * @var int
     */
    private $categoryIncomeId = 0;

    /**
     * @var int
     */
    private $categoryExpenseId = 0;

    /**
     * @var bool
     */
    private $enableForecast = 0;

    /**
     * @var bool
     */
    private $autoForecast = 0;

    /**
     * @var int
     */
    private $manualForecast = 0;

    /**
     * @var bool
     */
    private $writeToOrderLog = 1;

    /**
     * @var array
     */
    private $savedSettings = [];

    /**
     * @var waAppSettingsModel
     */
    private $settingsModel;

    /**
     * @var array
     */
    private $incomeActions = ['pay', 'restore', 'complete'];

    /**
     * @var array
     */
    private $expenseActions = ['refund', 'delete', 'cancel'];

    /**
     * @var string[]
     */
    private $jsonSerializableProperties = [
        'enabled',
        'accountId',
        'accountByStorefronts',
        'categoryIncomeId',
        'categoryExpenseId',
        'enableForecast',
        'autoForecast',
        'manualForecast',
        'writeToOrderLog',
        'incomeActions',
        'expenseActions',
    ];

    /**
     * @var array|mixed
     */
    private $stat;

    /**
     * @var int
     */
    private $todayTransactions = 0;

    /**
     * cashShopScriptSettings constructor.
     */
    public function __construct()
    {
        $this->settingsModel = new waAppSettingsModel();
        $this->savedSettings = json_decode(
            $this->settingsModel->get(cashConfig::APP_ID, 'shopscript_integration'),
            true
        ) ?: [];
        $this->load($this->savedSettings);
        $statData = json_decode($this->settingsModel->get(cashConfig::APP_ID, 'shopscript_stat'), true) ?: [];
        $this->todayTransactions = ifset($statData, 'today_transactions', date('Y-m-d'), $this->todayTransactions);
    }

    /**
     * @param array $data
     *
     * @return cashShopSettings
     */
    public function load(array $data)
    {
        $data = array_merge($this->savedSettings, $data);
        foreach (get_class_vars(__CLASS__) as $varname => $value) {
            if (!in_array($varname, $this->jsonSerializableProperties)) {
                continue;
            }

            $this->$varname = ifset($data, $varname, $value);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param bool $enabled
     *
     * @return cashShopSettings
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;

        return $this;
    }

    /**
     * @return int
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * @param string $storefront
     *
     * @return int
     */
    public function getAccountByStorefront($storefront)
    {
        return isset($this->accountByStorefronts[$storefront]) ? $this->accountByStorefronts[$storefront] : 0;
    }

    /**
     * @return array
     */
    public function getAccountsWithStorefront()
    {
        return $this->accountByStorefronts;
    }

    /**
     * @return int
     */
    public function getCategoryIncomeId()
    {
        return $this->categoryIncomeId;
    }

    /**
     * @return int
     */
    public function getCategoryExpenseId()
    {
        return $this->categoryExpenseId;
    }

    /**
     * @return bool
     */
    public function isEnableForecast()
    {
        return $this->enableForecast;
    }

    /**
     * @return int
     */
    public function getManualForecast()
    {
        return $this->manualForecast;
    }

    /**
     * @return bool
     */
    public function isWriteToOrderLog()
    {
        return $this->writeToOrderLog;
    }

    public function save()
    {
        $this->settingsModel->set(
            cashConfig::APP_ID,
            'shopscript_integration',
            json_encode($this, JSON_UNESCAPED_UNICODE)
        );
    }

    public function saveStat()
    {
        $this->settingsModel->set(
            cashConfig::APP_ID,
            'today_transactions',
            json_encode(
                [
                    'today_transactions' => [date('Y-m-d') => $this->todayTransactions],
                ]
            )
        );
    }

    /**
     * @return bool
     */
    public function isAutoForecast()
    {
        return $this->autoForecast;
    }

    /**
     * @return array
     */
    public function getIncomeActions()
    {
        return $this->incomeActions;
    }

    /**
     * @return array
     */
    public function getExpenseActions()
    {
        return $this->expenseActions;
    }

    /**
     * @return int
     */
    public function getTodayTransactions()
    {
        return (int)$this->todayTransactions;
    }

    /**
     * @param int $inc
     *
     * @return $this
     */
    public function incTodayTransactionsCount($inc = 1)
    {
        $this->todayTransactions += $inc;

        return $this;
    }

    /**
     * @return bool
     */
    public function isTurnedOn()
    {
        return $this->isEnabled() && !$this->savedSettings['enabled'];
    }

    /**
     * @return bool
     */
    public function isTurnedOff()
    {
        return !$this->isEnabled() && $this->savedSettings['enabled'];
    }

    /**
     * @return bool
     */
    public function forecastTurnedOn()
    {
        return $this->isEnableForecast() && !$this->savedSettings['enable_forecast'];
    }

    /**
     * @return bool
     */
    public function forecastTurnedOff()
    {
        return !$this->isEnableForecast() && $this->savedSettings['enable_forecast'];
    }
}